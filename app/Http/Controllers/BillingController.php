<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentSupply;
use App\Models\Dentist;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\TreatmentPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', '')); // número o paciente
        $status = $request->get('status', 'all');        // all|draft|issued|paid|canceled
        $from = $request->get('from');
        $to = $request->get('to');

        $hasExplicitDates = !empty($from) || !empty($to);
        $filterFrom = $from ?: today()->format('Y-m-d');
        $filterTo   = $to ?: today()->format('Y-m-d');

        // query base para reutilizar en charts antes de paginar
        $queryBase = Invoice::with(['patient:id,first_name,last_name'])
            ->when($q, function ($qq) use ($q) {
                $qq->where('number', 'like', "%{$q}%")
                    ->orWhereHas('patient', function ($w) use ($q) {
                        $w->where(DB::raw("CONCAT(first_name,' ',last_name)"), 'like', "%{$q}%");
                    });
            })
            ->when($status !== 'all', fn ($qq) => $qq->where('status', $status))
            ->where(function ($w) use ($filterFrom, $filterTo, $hasExplicitDates) {
                $w->where(function ($sub) use ($filterFrom, $filterTo) {
                    $sub->whereDate('created_at', '>=', $filterFrom)
                        ->whereDate('created_at', '<=', $filterTo);
                });
                // Mostrar siempre pendientes/borradores si no hay un rango de fechas explícito
                if (!$hasExplicitDates) {
                    $w->orWhereIn('status', ['draft', 'issued']);
                }
            });

        $invoices = (clone $queryBase)
            ->orderByRaw("FIELD(status, 'draft', 'issued', 'paid', 'canceled')")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // precargar items y payments para totales
        $invoices->load(['items', 'payments']);

        // ── Métricas monetarias (globales, sin filtros) ──
        $totalCollected = Payment::sum('amount');
        $totalBalances  = DB::selectOne("
            SELECT COALESCE(SUM(grand_total - paid_total), 0) AS total
            FROM (
                SELECT i.id,
                       (SELECT COALESCE(SUM(ii.quantity * ii.unit_price), 0) FROM invoice_items ii WHERE ii.invoice_id = i.id) 
                         - COALESCE(i.discount, 0) AS grand_total,
                       (SELECT COALESCE(SUM(p.amount), 0) FROM payments p WHERE p.invoice_id = i.id) AS paid_total
                FROM invoices i
                WHERE i.status IN ('draft', 'issued')
            ) sub
            WHERE grand_total > paid_total
        ")->total ?? 0;
        $todayCollected = Payment::whereDate('paid_at', today())->sum('amount');

        // ── Reporte de Cobradores ──
        $cobradorId = $request->get('cobrador');
        if (auth()->user()->role !== 'admin') {
            $cobradorId = auth()->id();
        }
        
        $filterCobradorFrom = $from ?: today()->format('Y-m-d');
        $filterCobradorTo   = $to ?: today()->format('Y-m-d');

        // Pagos individuales con relaciones para el desglose
        $collectorPayments = Payment::with([
                'receiver:id,name',
                'invoice:id,number,patient_id',
                'invoice.patient:id,first_name,last_name',
            ])
            ->when($cobradorId, fn ($qq) => $qq->where('received_by', $cobradorId))
            ->whereDate('paid_at', '>=', $filterCobradorFrom)
            ->whereDate('paid_at', '<=', $filterCobradorTo)
            ->orderByDesc('paid_at')
            ->limit(200)
            ->get();

        // Agrupar por cobrador + fecha para el resumen con accordion
        $collectorReport = $collectorPayments->groupBy(function ($p) {
            return ($p->received_by ?? 0) . '|' . $p->paid_at->format('Y-m-d');
        })->map(function ($group) {
            $first = $group->first();
            return (object) [
                'received_by' => $first->received_by,
                'receiver'    => $first->receiver,
                'fecha'       => $first->paid_at->format('Y-m-d'),
                'total'       => $group->sum('amount'),
                'cantidad'    => $group->count(),
                'metodos'     => $group->pluck('method')->unique()->sort()->values()->all(),
                'subtotales_metodo' => $group->groupBy('method')->map(function ($methodGroup) {
                    return $methodGroup->sum('amount');
                })->toArray(),
                'payments'    => $group->values(),
            ];
        })->values();

        // Lista de usuarios que han cobrado alguna vez (para el select)
        $collectors = \App\Models\User::whereIn('id', Payment::select('received_by')->distinct())
            ->orderBy('name')
            ->get();
            
        // Graficas (Solo Admin)
        $chart1Labels = [];
        $chart1Invoiced = [];
        $chart1Collected = [];
        $chart2Labels = [];
        $chart2Data = [];

        if (auth()->user()->role === 'admin') {
            $hasExplicitDates = !empty($request->get('from')) || !empty($request->get('to'));
            
            // ── Gráficos: Barras por mes y Porcentaje por Tratamiento ──
            $chartQuery = Invoice::with(['items.treatment', 'items.service', 'payments']);
            if ($hasExplicitDates) {
                if ($from) $chartQuery->whereDate('created_at', '>=', $from);
                if ($to)   $chartQuery->whereDate('created_at', '<=', $to);
            } else {
                // Por defecto, histórico de los últimos 6 meses para que el gráfico mensual tenga sentido
                $chartQuery->whereDate('created_at', '>=', now()->subMonths(5)->startOfMonth());
            }
            
            $invoicesForCharts = $chartQuery->get();
            
            $monthlyData = [];
            $treatmentRevenue = [];

            foreach ($invoicesForCharts as $inv) {
                // Gráfico 1: Por mes
                $monthKey = $inv->created_at->format('Y-m');
                $monthLabel = ucfirst($inv->created_at->translatedFormat('M Y'));
                
                if (!isset($monthlyData[$monthKey])) {
                    $monthlyData[$monthKey] = [
                        'label' => $monthLabel,
                        'invoiced' => 0,
                        'collected' => 0,
                    ];
                }
                
                $invGrand = $inv->items->sum('total') - $inv->discount;
                $invPaid = $inv->payments->sum('amount');

                $monthlyData[$monthKey]['invoiced'] += $invGrand;
                $monthlyData[$monthKey]['collected'] += $invPaid;

                // Gráfico 2: Ingresos por tratamiento (Proporcional)
                if ($invPaid > 0 && $invGrand > 0) {
                    $ratio = min($invPaid / $invGrand, 1.0);
                    
                    foreach ($inv->items as $item) {
                        $treatName = 'Otros';
                        if ($item->treatment) {
                            $treatName = $item->treatment->name;
                        } elseif ($item->service) {
                            $treatName = $item->service->name;
                        } elseif ($item->description) {
                            $treatName = $item->description;
                        }

                        if (!isset($treatmentRevenue[$treatName])) {
                            $treatmentRevenue[$treatName] = 0;
                        }
                        $treatmentRevenue[$treatName] += ($item->total * $ratio);
                    }
                }
            }
            
            ksort($monthlyData);
            $chart1Labels = array_column($monthlyData, 'label');
            $chart1Invoiced = array_column($monthlyData, 'invoiced');
            $chart1Collected = array_column($monthlyData, 'collected');

            arsort($treatmentRevenue);
            $topTreatments = array_slice($treatmentRevenue, 0, 7, true);
            $otherTreatments = array_sum(array_slice($treatmentRevenue, 7));
            if ($otherTreatments > 0) {
                $topTreatments['Otros'] = $otherTreatments;
            }
            $chart2Labels = array_keys($topTreatments);
            $chart2Data = array_values($topTreatments);
        }

        return view('admin.billing.index', compact(
            'invoices', 'q', 'status', 'from', 'to',
            'chart1Labels', 'chart1Invoiced', 'chart1Collected',
            'chart2Labels', 'chart2Data',
            'totalCollected', 'totalBalances', 'todayCollected',
            'collectorReport', 'collectors', 'cobradorId'
        ));
    }

    public function searchAppointments(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 1) return response()->json([]);

        $appointments = \App\Models\Appointment::with(['patient:id,first_name,last_name,ci', 'service:id,name'])
            ->where(function($w) use ($q) {
                $w->where('id', 'like', "%{$q}%")
                  ->orWhereHas('patient', function($p) use ($q) {
                      $p->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$q}%")
                        ->orWhere('ci', 'like', "%{$q}%");
                  });
            })
            ->orderByDesc('date')
            ->limit(20)
            ->get();

        return response()->json($appointments);
    }

    /** Form crear */
    public function create()
    {
        $invoice = new Invoice([
            'status' => 'issued',
            'discount' => 0,
            'tax_percent' => 0,
        ]);

        $patients = Patient::orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'ci', 'phone', 'email']);

        $services = Service::where('active', true)
            ->orderBy('name')
            ->get();

        $dentists = Dentist::orderBy('name')->get(['id', 'name']);

        return view('admin.billing.create', compact(
            'invoice',
            'patients',
            'services',
            'dentists'
        ));
    }

    /** Guardar */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                // Paciente
                'patient_id' => ['nullable', 'exists:patients,id'],
                'ci' => ['nullable', 'string', 'max:20'],
                'first_name' => ['nullable', 'string', 'max:100'],
                'last_name' => ['nullable', 'string', 'max:100'],
                'phone' => ['nullable', 'string', 'max:20'],

                // Opcional compatibilidad
                'appointment_id' => ['nullable', 'exists:appointments,id'],
                'treatment_plan_id' => ['nullable', 'exists:treatment_plans,id'],

                // Config factura
                'discount' => ['nullable', 'numeric', 'min:0'],
                'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'notes' => ['nullable', 'string', 'max:500'],

                // Ítems (cada ítem = UNA cita)
                'items' => ['required', 'array', 'min:1'],
                'items.*.service_id' => ['required', 'exists:services,id'],
                'items.*.description' => ['nullable', 'string', 'max:255'],
                'items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:9999'],
                'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999'],
                'items.*.dentist_id' => ['required', 'exists:dentists,id'],

                // Pago inmediato (lo dejamos por compatibilidad, pero la vista no lo usa)
                'pay_amount' => ['nullable', 'numeric', 'min:0'],
                'pay_method' => ['nullable', 'in:cash,card,transfer,wallet'],
                'pay_reference' => ['nullable', 'string', 'max:120'],
            ]);



            DB::transaction(function () use (&$invoice, $data, $request) {
                $userId = optional($request->user())->id;

                // -------------------------------------
                // 1) Resolver PACIENTE (por id o por CI)
                // -------------------------------------
                if (empty($data['patient_id'])) {
                    if (empty($data['ci'])) {
                        throw ValidationException::withMessages([
                            'ci' => 'Debes seleccionar un paciente o ingresar un CI.',
                        ]);
                    }

                    $patient = Patient::where('ci', $data['ci'])->first();

                    if (! $patient) {
                        if (empty($data['first_name']) || empty($data['last_name'])) {
                            throw ValidationException::withMessages([
                                'first_name' => 'Nombre y apellido son obligatorios para registrar un nuevo paciente.',
                            ]);
                        }

                        $patient = Patient::create([
                            'ci' => $data['ci'],
                            'first_name' => $data['first_name'],
                            'last_name' => $data['last_name'],
                            'phone' => $data['phone'] ?? null,
                        ]);
                    }

                    $data['patient_id'] = $patient->id;
                }

                // -------------------------------------
                // 2) Número secuencial de factura
                // -------------------------------------
                $last = Invoice::orderByDesc('id')->value('number');
                $nextSeq = 1;
                if ($last && preg_match('/(\d+)$/', $last, $m)) {
                    $nextSeq = ((int) $m[1]) + 1;
                }
                $number = 'REC-'.str_pad($nextSeq, 6, '0', STR_PAD_LEFT);

                // -------------------------------------
                // 3) Crear factura base
                // -------------------------------------
                $invoice = Invoice::create([
                    'number' => $number,
                    'patient_id' => $data['patient_id'],
                    'appointment_id' => $data['appointment_id'] ?? null,
                    'treatment_plan_id' => $data['treatment_plan_id'] ?? null,
                    'status' => 'issued',
                    'discount' => $data['discount'] ?? 0,
                    'tax_percent' => $data['tax_percent'] ?? 0,
                    'issued_at' => now(),
                    'notes' => $data['notes'] ?? null,
                    'created_by' => $userId,
                ]);

                // -------------------------------------
                // 4) Ítems (solo datos económicos)
                // -------------------------------------
                $rows = [];
                $subtotal = 0.0;

                foreach ($data['items'] as $it) {
                    $qty = (int) ($it['quantity'] ?? 1);
                    $unit = (float) $it['unit_price'];
                    $total = $qty * $unit;
                    $subtotal += $total;
                    $description = trim((string) ($it['description'] ?? ''));

                    if ($description === '') {
                        $description = Service::whereKey($it['service_id'])->value('name') ?? 'Servicio';
                    }

                    $rows[] = [
                        'invoice_id' => $invoice->id,
                        'service_id' => $it['service_id'],
                        'treatment_id' => $it['treatment_id'] ?? null,
                        'description' => $description,
                        'quantity' => $qty,
                        'unit_price' => $unit,
                        'total' => $total,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                InvoiceItem::insert($rows);

                // -------------------------------------
                // 5) Totales de factura
                // -------------------------------------
                $discount = (float) ($data['discount'] ?? 0);
                $taxPercent = (float) ($data['tax_percent'] ?? 0);
                $base = max($subtotal - $discount, 0);
                $grandTotal = $base + ($base * $taxPercent / 100);

                // -------------------------------------
                // 6) Pago inmediato (opcional)
                // -------------------------------------
                $amount = (float) ($data['pay_amount'] ?? 0);
                $method = $data['pay_method'] ?? null;

                if ($amount > 0 && $method) {
                    \App\Models\Payment::create([
                        'invoice_id' => $invoice->id,
                        'amount' => $amount,
                        'method' => $method,
                        'reference' => $request->input('pay_reference'),
                        'paid_at' => now(),
                        'received_by' => $userId,
                    ]);

                    if ($amount + 0.0001 >= $grandTotal) {
                        $invoice->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);
                    }
                }
            });

            return redirect()
                ->route('admin.billing.show', $invoice)
                ->with('ok', 'Recibo y citas creadas correctamente.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Error al crear recibo presencial', [
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Ocurrió un error al crear el recibo. Inténtalo de nuevo o avisa al administrador.']);
        }
    }

    /** Ver detalle */
    public function show(Invoice $invoice)
    {
        $invoice->load(['patient', 'items.service', 'payments']);
        $tot = $this->computeTotals($invoice);
        $pdfRelPath = 'invoices/invoice_'.$invoice->number.'.pdf';
        $pdfExists = Storage::disk('public')->exists($pdfRelPath);

        return view('admin.billing.show', array_merge(
            ['invoice' => $invoice, 'pdfExists' => $pdfExists, 'pdfRelPath' => $pdfRelPath],
            $tot
        ));
    }

    /** Form editar (solo draft/issued y sin pagos) */
    public function edit(Invoice $invoice)
    {
        abort_if(in_array($invoice->status, ['paid', 'canceled']), 403, 'No editable en este estado.');
        abort_if($invoice->payments()->exists(), 403, 'No editable con pagos registrados.');

        $invoice->load(['items', 'patient']);
        $patients = Patient::orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name']);
        $services = Service::where('active', true)->orderBy('name')->get(['id', 'name', 'price']);

        return view('admin.billing.edit', compact('invoice', 'patients', 'services'));
    }

    /** Actualizar */
    public function update(Request $request, Invoice $invoice)
    {
        abort_if(in_array($invoice->status, ['paid', 'canceled']), 403);
        abort_if($invoice->payments()->exists(), 403);

        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.service_id' => ['nullable', 'exists:services,id'],
            'items.*.treatment_id' => ['nullable', 'exists:treatments,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999'],
        ]);

        DB::transaction(function () use ($invoice, $data) {
            $invoice->update([
                'patient_id' => $data['patient_id'],
                'discount' => $data['discount'] ?? 0,
                'tax_percent' => $data['tax_percent'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->items()->delete();

            $rows = [];
            foreach ($data['items'] as $it) {
                $qty = (int) $it['quantity'];
                $unit = (float) $it['unit_price'];
                $rows[] = [
                    'invoice_id' => $invoice->id,
                    'service_id' => $it['service_id'] ?? null,
                    'treatment_id' => $it['treatment_id'] ?? null,
                    'description' => $it['description'],
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'total' => $qty * $unit,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            InvoiceItem::insert($rows);
        });

        return redirect()->route('admin.billing.show', $invoice)->with('ok', 'Recibo actualizado.');
    }

    /** Emitir (si estaba draft) */
    public function issue(Invoice $invoice)
    {
        if ($invoice->status === 'draft') {
            $invoice->update(['status' => 'issued', 'issued_at' => now()]);

            return back()->with('ok', 'Recibo emitido.');
        }

        return back()->withErrors('El recibo no está en borrador.');
    }

    /** Cancelar (no debe estar pagada) */
    public function cancel(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->withErrors('No puedes cancelar un recibo pagado.');
        }
        $invoice->update(['status' => 'canceled']);

        return back()->with('ok', 'Recibo cancelado.');
    }

    /** Agregar pago */
    public function addPayment(Request $request, Invoice $invoice)
    {
        abort_if($invoice->status === 'canceled', 403, 'Recibo cancelado.');
        $invoice->load(['items', 'payments']);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::in(['cash', 'card', 'transfer', 'wallet'])],
            'reference' => ['nullable', 'string', 'max:120'],
            'paid_at' => ['nullable', 'date'],
        ]);

        $balance = $invoice->balance;
        if ($data['amount'] > $balance + 0.0001) {
            return back()->withErrors('El monto excede el saldo.')->withInput();
        }

        Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $data['amount'],
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'paid_at' => $data['paid_at'] ?? now(),
            'received_by' => optional($request->user())->id,
        ]);

        return back()->with('ok', 'Pago registrado.');
    }

    /** Eliminar pago (si quieres permitir) */
    public function deletePayment(Invoice $invoice, Payment $payment)
    {
        abort_if($payment->invoice_id !== $invoice->id, 404);
        $payment->delete();

        // recalcular estado
        $invoice->refresh()->load(['items', 'payments']);
        if ($invoice->status === 'paid' && $invoice->balance > 0) {
            $invoice->update(['status' => 'issued', 'paid_at' => null]);
        }

        return back()->with('ok', 'Pago eliminado.');
    }

    /** Eliminar factura (sin pagos) */
    public function destroy(Invoice $invoice)
    {
        return back()->withErrors('Los recibos no pueden ser eliminados, solo pueden ser anulados.');
    }

    public function createFromPlan(TreatmentPlan $plan)
    {
        $plan->load(['patient', 'treatments.service']);
        $items = $plan->treatments;
        $subtotal = (float) $items->sum('price');
        $discount = 0.00;
        $taxPct = 0.00;
        $tax = round($subtotal * $taxPct / 100, 2);
        $grand = max(0, round($subtotal - $discount + $tax, 2));

        return view('admin.billing.from_plan', compact('plan', 'items', 'subtotal', 'discount', 'taxPct', 'tax', 'grand'));
    }

    // Generar factura + items + (opcional) pago
    public function storeFromPlan(Request $request, \App\Models\TreatmentPlan $plan)
    {
        $data = $request->validate([
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pay_now' => ['nullable', 'boolean'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'method' => ['nullable', 'in:cash,card,transfer,wallet'],
            'reference' => ['nullable', 'string', 'max:120'], // <-- NUEVO
            'notes' => ['nullable', 'string'],
        ]);

        $plan->load(['patient', 'treatments.service']);
        $items = $plan->treatments;
        $subtotal = (float) $items->sum('price');
        $discount = (float) ($data['discount'] ?? 0);
        $taxPct = (float) ($data['tax_percent'] ?? 0);
        $tax = round($subtotal * $taxPct / 100, 2);
        $grand = max(0, round($subtotal - $discount + $tax, 2));

        return DB::transaction(function () use ($plan, $items, $discount, $taxPct, $grand, $data) {

            $inv = Invoice::create([
                'number' => $this->nextNumber(),
                'patient_id' => $plan->patient_id,
                'treatment_plan_id' => $plan->id,
                'status' => 'issued',
                'discount' => $discount,
                'tax_percent' => $taxPct,
                'issued_at' => now(),
                'notes' => $data['notes'] ?? null,
                'created_by' => optional(auth()->user())->id,
            ]);

            foreach ($items as $t) {
                $desc = $t->service?->name ?? 'Servicio';
                if ($t->tooth_code) {
                    $desc .= ' · Pieza '.$t->tooth_code.($t->surface ? ' '.$t->surface : '');
                }
                InvoiceItem::create([
                    'invoice_id' => $inv->id,
                    'service_id' => $t->service_id,
                    'treatment_id' => $t->id,
                    'description' => $desc,
                    'quantity' => 1,
                    'unit_price' => $t->price,
                    'total' => $t->price,
                ]);
            }

            if (! empty($data['pay_now'])) {
                $amount = min((float) ($data['amount'] ?? 0), $grand);
                if ($amount > 0 && ! empty($data['method'])) {
                    Payment::create([
                        'invoice_id' => $inv->id,
                        'amount' => $amount,
                        'method' => $data['method'],             // enum válido
                        'reference' => $data['reference'] ?? null,  // <-- FIX AQUÍ
                        'paid_at' => now(),
                        'received_by' => optional(auth()->user())->id,
                    ]);
                }
            }

            $paidSum = (float) Payment::where('invoice_id', $inv->id)->sum('amount');
            if ($paidSum >= $grand && $grand > 0) {
                $inv->update(['status' => 'paid', 'paid_at' => now()]);
            }

            return redirect()->route('admin.invoices.show', $inv)->with('ok', 'Recibo #'.$inv->number.' creado.');
        });
    }

    private function nextNumber(): string
    {
        $year = date('Y');
        $last = Invoice::where('number', 'like', $year.'-%')
            ->orderBy('number', 'desc')
            ->value('number'); // ej. "2025-0042"

        $seq = 1;
        if ($last && preg_match('/^\d{4}\-(\d{4})$/', $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return sprintf('%s-%04d', $year, $seq);
    }

    public function storePayment(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,card,transfer,wallet'],
            'reference' => ['nullable', 'string', 'max:120'],
        ]);

        $balance = $invoice->balance;
        if ($data['amount'] > $balance + 0.0001) {
            return back()->withErrors('El monto excede el saldo pendiente.')->withInput();
        }

        Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $data['amount'],
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'paid_at' => now(),
            'received_by' => optional($request->user())->id,
        ]);

        // Recalcular totales con el nuevo pago
        $invoice->refresh()->load(['items', 'payments']);
        $tot = $this->computeTotals($invoice); // asumo que ya tenías este método

        if ($tot['grand'] > 0 && $tot['balance'] <= 0.0001) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Si es factura sin cita asociada (presencial),
            // crear las citas a partir de los ítems
            $this->createAppointmentsFromInvoice($invoice);

            $this->renderAndStorePdf($invoice);

            return redirect()
                ->route('admin.invoices.show', $invoice)
                ->with('ok', 'Pago registrado. Recibo saldado.')
                ->with('open_pdf', true);
        }

        // Auto-regenerar PDF para reflejar el nuevo pago aunque no esté saldado
        $this->renderAndStorePdf($invoice);

        return back()->with('ok', 'Pago registrado.');
    }

    public function markPaid(Invoice $invoice)
    {
        $tot = $this->computeTotals($invoice);
        if ($tot['grand'] > 0 && $tot['balance'] <= 0) {
            $invoice->update(['status' => 'paid', 'paid_at' => now()]);
            $this->renderAndStorePdf($invoice);

            return redirect()
                ->route('admin.invoices.show', $invoice)
                ->with('ok', 'Recibo pagado. Comprobante listo.')
                ->with('open_pdf', true);
        }

        return back()->with('warn', 'Aún hay saldo pendiente; registra el pago primero.');
    }

    public function print(Invoice $invoice)
    {
        if ($invoice->status === 'canceled') {
            return back()->withErrors('No se puede imprimir un recibo anulado.');
        }

        $invoice->load(['patient', 'items.service', 'payments']);
        $tot = $this->computeTotals($invoice);

        return view('admin.billing.print', array_merge(['invoice' => $invoice], $tot));
    }

    public function pdf(Invoice $invoice)
    {
        if ($invoice->status === 'canceled') {
            return back()->withErrors('No se puede descargar un recibo anulado.');
        }

        $invoice->load(['patient', 'items.service', 'payments']);
        $tot = $this->computeTotals($invoice);

        if (! class_exists(Pdf::class)) {
            return redirect()->route('admin.invoices.print', $invoice)
                ->with('warn', 'Instala barryvdh/laravel-dompdf para descargar PDF.');
        }

        $pdf = Pdf::loadView('admin.billing.print', array_merge(['invoice' => $invoice], $tot))
            ->setPaper('a4');

        $relPath = 'invoices/invoice_'.$invoice->number.'.pdf';
        Storage::disk('public')->put($relPath, $pdf->output()); // guarda comprobante

        // descarga inmediata
        return $pdf->download('recibo_'.$invoice->number.'.pdf');
    }

    public function pdfExport(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $q = trim((string) $request->get('q', ''));
        $status = $request->get('status', 'all');
        $from = $request->get('from');
        $to = $request->get('to');

        $hasExplicitDates = !empty($from) || !empty($to);
        $filterFrom = $from ?: today()->format('Y-m-d');
        $filterTo   = $to ?: today()->format('Y-m-d');

        // Misma query que Index
        $query = Invoice::with(['patient:id,first_name,last_name', 'items', 'payments'])
            ->when($q, function ($qq) use ($q) {
                $qq->where('number', 'like', "%{$q}%")
                    ->orWhereHas('patient', function ($w) use ($q) {
                        $w->where(DB::raw("CONCAT(first_name,' ',last_name)"), 'like', "%{$q}%");
                    });
            })
            ->when($status !== 'all', fn ($qq) => $qq->where('status', $status))
            ->where(function ($w) use ($filterFrom, $filterTo, $hasExplicitDates) {
                $w->where(function ($sub) use ($filterFrom, $filterTo) {
                    $sub->whereDate('created_at', '>=', $filterFrom)
                        ->whereDate('created_at', '<=', $filterTo);
                });
                // Mostrar siempre pendientes/borradores si no hay un rango de fechas explícito
                if (!$hasExplicitDates) {
                    $w->orWhereIn('status', ['draft', 'issued']);
                }
            })
            ->orderByDesc('created_at');

        // Para evitar problemas de memoria, limitamos a 500 o usamos chunking si fuera masivo.
        // Dado el uso, 500 es razonable.
        $invoices = $query->limit(500)->get();

        // Calculo de totales en PHP
        $totalInvoiced = 0;
        $totalPaid = 0;
        $totalPending = 0;

        foreach ($invoices as $inv) {
            $tot = $this->computeTotals($inv); // Reutilizamos logica centralizada
            $totalInvoiced += $tot['grand'];
            $totalPaid += $tot['paid'];
            $totalPending += $tot['balance'];

            // Inyectamos valores calculados para la vista
            $inv->calc_total = $tot['grand'];
            $inv->calc_paid = $tot['paid'];
            $inv->calc_balance = $tot['balance'];
        }

        // Desglose por método de pago
        $allPayments = $invoices->pluck('payments')->flatten();
        $paymentsByMethod = $allPayments->groupBy('method')->map(function ($group, $method) {
            return (object) [
                'method' => $method,
                'count'  => $group->count(),
                'total'  => $group->sum('amount'),
            ];
        })->sortByDesc('total')->values();

        $pdf = Pdf::loadView('admin.billing.pdf', [
            'invoices' => $invoices,
            'totalInvoiced' => $totalInvoiced,
            'totalPaid' => $totalPaid,
            'totalPending' => $totalPending,
            'paymentsByMethod' => $paymentsByMethod,
            'filters' => compact('q', 'status', 'from', 'to'),
            'user' => auth()->user(),
        ])->setPaper('a4', 'landscape'); // Landscape mejor para tablas financieras

        return $pdf->download('reporte_pagos_'.now()->format('YmdHis').'.pdf');
    }

    /**
     * PDF de reporte de cobradores.
     * Sin fechas → hoy. Sin cobrador → todos.
     */
    public function collectorsPdfExport(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'cajero', 'asistente'])) {
            abort(403);
        }

        $cobradorId = $request->get('cobrador');
        if ($user->role !== 'admin') {
            $cobradorId = $user->id;
        }
        
        $from       = $request->get('from');
        $to         = $request->get('to');

        // Si no hay rango de fechas, default al día actual
        if (! $from && ! $to) {
            $from = today()->format('Y-m-d');
            $to   = today()->format('Y-m-d');
        }

        $payments = Payment::with([
                'receiver:id,name',
                'invoice:id,number,patient_id',
                'invoice.patient:id,first_name,last_name',
            ])
            ->when($cobradorId, fn ($qq) => $qq->where('received_by', $cobradorId))
            ->when($from, fn ($qq) => $qq->whereDate('paid_at', '>=', $from))
            ->when($to, fn ($qq) => $qq->whereDate('paid_at', '<=', $to))
            ->orderBy('received_by')
            ->orderByDesc('paid_at')
            ->limit(500)
            ->get();

        // Agrupar por cobrador → fecha
        $grouped = $payments->groupBy(function ($p) {
            return $p->receiver->name ?? 'Sin asignar';
        })->map(function ($byCollector) {
            return $byCollector->groupBy(fn ($p) => $p->paid_at->format('Y-m-d'));
        });

        $totalAmount = $payments->sum('amount');
        $totalCount  = $payments->count();
        $subtotalsByMethod = $payments->groupBy('method')->map(function ($group) {
            return $group->sum('amount');
        })->toArray();

        // Nombre del cobrador seleccionado (o "Todos")
        $collectorName = 'Todos los cobradores';
        if ($cobradorId) {
            $collectorName = \App\Models\User::find($cobradorId)?->name ?? 'Cobrador #'.$cobradorId;
        }

        $pdf = Pdf::loadView('admin.billing.collectors-pdf', [
            'grouped'           => $grouped,
            'totalAmount'       => $totalAmount,
            'totalCount'        => $totalCount,
            'subtotalsByMethod' => $subtotalsByMethod,
            'collectorName'     => $collectorName,
            'dateFrom'          => $from,
            'dateTo'            => $to,
            'user'              => auth()->user(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('reporte_cobradores_'.now()->format('YmdHis').'.pdf');
    }

    // metodos pdf

    public function view(Invoice $invoice)
    {
        $relPath = 'invoices/invoice_'.$invoice->number.'.pdf';
        $absPath = storage_path('app/public/'.$relPath);

        if (! Storage::disk('public')->exists($relPath)) {
            // no existe?? solo avisar
            return back()->with('warn', 'No existe el comprobante. Usa “Regenerar PDF”.');
        }

        return response()->file($absPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="recibo_'.$invoice->number.'.pdf"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    public function download(Invoice $invoice)
    {
        if ($invoice->status === 'canceled') {
            return back()->withErrors('No se puede descargar un recibo anulado.');
        }

        $relPath = 'invoices/invoice_'.$invoice->number.'.pdf';
        if (! Storage::disk('public')->exists($relPath)) {
            return back()->with('warn', 'No existe el comprobante. Regénéralo.');
        }

        return response()->download(storage_path('app/public/'.$relPath), 'recibo_'.$invoice->number.'.pdf');
    }

    public function regenerate(Invoice $invoice)
    {
        if ($invoice->status === 'canceled') {
            return back()->withErrors('No se puede generar PDF de un recibo anulado.');
        }

        if (! class_exists(Pdf::class)) {
            return back()->with('warn', 'Instala barryvdh/laravel-dompdf para generar PDF.');
        }

        $invoice->load(['patient', 'items.service', 'payments']);
        $tot = $this->computeTotals($invoice);

        $pdf = Pdf::loadView('admin.billing.print', array_merge(['invoice' => $invoice], $tot))
            ->setPaper('a4');

        $relPath = 'invoices/invoice_'.$invoice->number.'.pdf';
        $absPath = storage_path('app/public/'.$relPath);

        Storage::disk('public')->put($relPath, $pdf->output()); // (re)genera y guarda

        // forzar descarga del archivo recien generado
        return response()->download($absPath, 'recibo_'.$invoice->number.'.pdf');
    }

    private function computeTotals(Invoice $invoice): array
    {
        $invoice->loadMissing(['items', 'payments']);

        // Subtotal = suma de totales de items (si no hay total guardado, calculamos)
        $subtotal = 0.0;
        foreach ($invoice->items as $it) {
            $qty = (int) ($it->quantity ?? 1);
            $unit = (float) ($it->unit_price ?? 0);
            $line = (float) ($it->total ?? ($qty * $unit));
            $subtotal += $line;
        }

        $discount = (float) ($invoice->discount ?? 0);
        $taxPercent = (float) ($invoice->tax_percent ?? 0);

        // Base después de descuento
        $base = max($subtotal - $discount, 0);

        // Impuesto calculado sobre base
        $tax = round($base * ($taxPercent / 100), 2);

        // Total final
        $grand = round($base + $tax, 2);

        // Pagado
        $paid = 0.0;
        foreach ($invoice->payments as $p) {
            $paid += (float) ($p->amount ?? 0);
        }
        $paid = round($paid, 2);

        // Saldo
        $balance = round(max($grand - $paid, 0), 2);

        return compact('subtotal', 'tax', 'grand', 'paid', 'balance');
    }

    public function createFromAppointment(Appointment $appointment)
    {
        if ($appointment->status === 'canceled') {
            return redirect()
                ->route('admin.appointments.show', $appointment)
                ->with('error', 'No se puede crear un recibo para una cita cancelada.');
        }

        // ¿Ya tiene factura?
        $existing = \App\Models\Invoice::where('appointment_id', $appointment->id)
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            return redirect()
                ->route('admin.invoices.show', $existing)
                ->with('info', 'Esta cita ya tiene un recibo, te llevé al mismo.');
        }

        $appointment->load(['patient', 'service', 'dentist']);
        $services = Service::orderBy('name')->get();

        return view('admin.billing.create_from_appointment', compact('appointment', 'services'));
    }

    protected function createAppointmentsFromInvoice(Invoice $invoice): void
    {
        // Si ya tiene cita enlazada, no hacemos nada
        if ($invoice->appointment_id) {
            return;
        }

        $invoice->loadMissing('items.service');

        $firstAppointmentId = null;

        foreach ($invoice->items as $item) {
            // Solo consideramos ítems que representen un servicio (cita)
            if (
                ! $item->service_id ||
                ! $item->dentist_id ||
                ! $item->date ||
                ! $item->start_time
            ) {
                continue;
            }

            $service = $item->service ?? Service::find($item->service_id);
            if (! $service) {
                continue;
            }

            $start = Carbon::parse($item->date.' '.$item->start_time);
            $end = $start->copy()->addMinutes($service->duration_min ?? 30);

            // Evitar choque con citas ya existentes
            $conflict = Appointment::where('dentist_id', $item->dentist_id)
                ->whereDate('date', $start->toDateString())
                ->active()
                ->where('start_time', '<', $end->format('H:i:s'))
                ->where('end_time', '>', $start->format('H:i:s'))
                ->exists();

            if ($conflict) {
                throw new \RuntimeException("Conflicto de horario al crear cita para el recibo {$invoice->number}.");
            }

            // Determinar silla igual que en AppointmentController@store
            $dow = $start->dayOfWeek;
            $block = Schedule::where('dentist_id', $item->dentist_id)
                ->where('day_of_week', $dow)
                ->where('start_time', '<=', $start->format('H:i:s'))
                ->where('end_time', '>=', $end->format('H:i:s'))
                ->orderBy('start_time', 'desc')
                ->first();

            $chairId = $block->chair_id ?? Dentist::whereKey($item->dentist_id)->value('chair_id');

            if (! $chairId) {
                throw new \RuntimeException("No hay silla asignada para la cita generada desde el recibo {$invoice->number}.");
            }

            $appointment = Appointment::create([
                'patient_id' => $invoice->patient_id,
                'dentist_id' => $item->dentist_id,
                'service_id' => $item->service_id,
                'chair_id' => $chairId,
                'date' => $start->toDateString(),
                'start_time' => $start->format('H:i:s'),
                'end_time' => $end->format('H:i:s'),
                'status' => 'done',    // o el estado que uses para "pagada/atendida"
                'is_active' => true,
                'notes' => 'Cita generada automáticamente desde factura '.$invoice->number,
            ]);

            if (! $firstAppointmentId) {
                $firstAppointmentId = $appointment->id;
            }
        }

        if ($firstAppointmentId && ! $invoice->appointment_id) {
            $invoice->appointment_id = $firstAppointmentId;
            $invoice->save();
        }
    }

    protected function roundToNextSlot(Carbon $time, int $slotMinutes): Carbon
    {
        $minutes = (int) $time->format('i');
        $seconds = (int) $time->format('s');

        if ($minutes % $slotMinutes === 0 && $seconds === 0) {
            return $time->copy();
        }

        $mod = $minutes % $slotMinutes;
        $add = $slotMinutes - $mod;

        return $time->copy()->addMinutes($add)->seconds(0);
    }

    public function storeFromAppointment(Request $request, Appointment $appointment)
    {
        if ($appointment->status === 'canceled') {
            return redirect()
                ->route('admin.appointments.show', $appointment)
                ->with('error', 'No se puede crear un recibo para una cita cancelada.');
        }

        $data = $request->validate([
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.service_id' => ['nullable', 'exists:services,id'],
            'items.*.treatment_id' => ['nullable', 'exists:treatments,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999'],
        ]);

        $userId = optional($request->user())->id;

        DB::transaction(function () use (&$invoice, $data, $userId, $appointment) {

            // === Número secuencial (FAC-000001, FAC-000002, ...) ===
            $last = Invoice::orderByDesc('id')->value('number');
            $nextSeq = 1;
            if ($last && preg_match('/(\d+)$/', $last, $m)) {
                $nextSeq = ((int) $m[1]) + 1;
            }
            $number = 'FAC-'.str_pad($nextSeq, 6, '0', STR_PAD_LEFT);

            // === Crear factura base ===
            $invoice = Invoice::create([
                'number' => $number,
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'treatment_plan_id' => null,
                'status' => 'issued',
                'discount' => $data['discount'] ?? 0,
                'tax_percent' => $data['tax_percent'] ?? 0,
                'issued_at' => now(),
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            // === Ítems ===
            $rows = [];
            $subtotal = 0.0;

            foreach ($data['items'] as $it) {
                $qty = (int) $it['quantity'];

                $serviceId = $it['service_id'] ?? null;

                // ✅ SI hay service_id: el precio unitario sale del Service (con descuento) sí o sí
                if ($serviceId) {
                    $service = Service::find($serviceId);

                    $unit = $service
                        ? (float) $service->priceEffective(now())
                        : (float) $it['unit_price'];

                    $desc = $it['description'] ?? null;
                    if (! $desc && $service) {
                        $desc = $service->name;
                    }
                } else {
                    // Si es ítem manual sin service_id
                    $unit = (float) $it['unit_price'];
                    $desc = $it['description'] ?? null;
                }

                $total = $qty * $unit;
                $subtotal += $total;

                $rows[] = [
                    'invoice_id' => $invoice->id,
                    'service_id' => $serviceId,
                    'treatment_id' => $it['treatment_id'] ?? null,
                    'description' => $desc,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'total' => $total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            InvoiceItem::insert($rows);

            // === Insumos de la cita como ítem adicional ===
            $suppliesTotal = AppointmentSupply::where('appointment_id', $appointment->id)
                ->selectRaw('COALESCE(SUM(qty * COALESCE(unit_cost_at_issue,0)),0) as total')
                ->value('total');

            if ($suppliesTotal > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => null,
                    'treatment_id' => null,
                    'description' => 'Insumos utilizados (cita #'.$appointment->id.')',
                    'quantity' => 1,
                    'unit_price' => (float) $suppliesTotal,
                    'total' => (float) $suppliesTotal,
                ]);

                $subtotal += (float) $suppliesTotal;
            }

            // Totales los calcula tu Invoice por accessors, ok.
        });

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('ok', 'Recibo creado. Ahora registra los pagos desde esta pantalla.');
    }

    private function renderAndStorePdf(Invoice $invoice): void
    {
        $invoice->load(['patient', 'items.service', 'payments']);
        $tot = $this->computeTotals($invoice);

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView(
                'admin.billing.print',
                array_merge(['invoice' => $invoice], $tot)
            )->setPaper('a4');

            $relPath = 'invoices/invoice_'.$invoice->number.'.pdf';
            Storage::disk('public')->put($relPath, $pdf->output());
        }
    }
}
