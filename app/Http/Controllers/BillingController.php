<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\TreatmentPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $q      = trim((string) $request->get('q', '')); // número o paciente
        $status = $request->get('status', 'all');        // all|draft|issued|paid|canceled
        $from   = $request->get('from');
        $to     = $request->get('to');

        $invoices = Invoice::with(['patient:id,first_name,last_name'])
            ->when($q, function ($qq) use ($q) {
                $qq->where('number', 'like', "%{$q}%")
                    ->orWhereHas('patient', function ($w) use ($q) {
                        $w->where(DB::raw("CONCAT(first_name,' ',last_name)"), 'like', "%{$q}%");
                    });
            })
            ->when($status !== 'all', fn($qq) => $qq->where('status', $status))
            ->when($from, fn($qq) => $qq->whereDate('created_at', '>=', $from))
            ->when($to,   fn($qq) => $qq->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->paginate(15)->withQueryString();

        // precargar items y payments para totales
        $invoices->load(['items', 'payments']);

        return view('admin.billing.index', compact('invoices', 'q', 'status', 'from', 'to'));
    }

    /** Form crear */
    public function create()
    {
        $invoice = new Invoice([
            'status' => 'issued',
            'discount' => 0,
            'tax_percent' => 0,
        ]);

        $patients = Patient::orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name']);
        $services = Service::where('active', true)->orderBy('name')->get(['id', 'name', 'price']);

        return view('admin.billing.create', compact('invoice', 'patients', 'services'));
    }

    /** Guardar */
    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'treatment_plan_id' => ['nullable', 'exists:treatment_plans,id'],
            'discount'   => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'      => ['nullable', 'string', 'max:500'],

            // items[]
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.description'   => ['required', 'string', 'max:255'],
            'items.*.service_id'    => ['nullable', 'exists:services,id'],
            'items.*.treatment_id'  => ['nullable', 'exists:treatments,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1', 'max:9999'],
            'items.*.unit_price'    => ['required', 'numeric', 'min:0', 'max:9999999'],
        ]);

        $userId = optional($request->user())->id;

        DB::transaction(function () use (&$invoice, $data, $userId) {
            // número secuencial
            $last = Invoice::orderByDesc('id')->value('number');
            $nextSeq = 1;
            if ($last && preg_match('/(\d+)$/', $last, $m)) {
                $nextSeq = ((int)$m[1]) + 1;
            }
            $number = 'FAC-' . str_pad($nextSeq, 6, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'number' => $number,
                'patient_id' => $data['patient_id'],
                'appointment_id' => $data['appointment_id'] ?? null,
                'treatment_plan_id' => $data['treatment_plan_id'] ?? null,
                'status' => 'issued', // arranca emitida
                'discount' => $data['discount'] ?? 0,
                'tax_percent' => $data['tax_percent'] ?? 0,
                'issued_at' => now(),
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            $rows = [];
            foreach ($data['items'] as $it) {
                $qty  = (int)$it['quantity'];
                $unit = (float)$it['unit_price'];
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

        return redirect()->route('admin.billing.show', $invoice)->with('ok', 'Factura creada.');
    }

    /** Ver detalle */
    public function show(Invoice $invoice)
    {
        $invoice->load(['patient', 'items.service', 'payments']);
        $tot = $this->computeTotals($invoice);
        $pdfRelPath = 'invoices/invoice_' . $invoice->number . '.pdf';
        $pdfExists  = Storage::disk('public')->exists($pdfRelPath);

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
            'discount'   => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'      => ['nullable', 'string', 'max:500'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.description'   => ['required', 'string', 'max:255'],
            'items.*.service_id'    => ['nullable', 'exists:services,id'],
            'items.*.treatment_id'  => ['nullable', 'exists:treatments,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1', 'max:9999'],
            'items.*.unit_price'    => ['required', 'numeric', 'min:0', 'max:9999999'],
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
                $qty  = (int)$it['quantity'];
                $unit = (float)$it['unit_price'];
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

        return redirect()->route('admin.billing.show', $invoice)->with('ok', 'Factura actualizada.');
    }

    /** Emitir (si estaba draft) */
    public function issue(Invoice $invoice)
    {
        if ($invoice->status === 'draft') {
            $invoice->update(['status' => 'issued', 'issued_at' => now()]);
            return back()->with('ok', 'Factura emitida.');
        }
        return back()->withErrors('La factura no está en borrador.');
    }

    /** Cancelar (no debe estar pagada) */
    public function cancel(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->withErrors('No puedes cancelar una factura pagada.');
        }
        $invoice->update(['status' => 'canceled']);
        return back()->with('ok', 'Factura cancelada.');
    }

    /** Agregar pago */
    public function addPayment(Request $request, Invoice $invoice)
    {
        abort_if($invoice->status === 'canceled', 403, 'Factura cancelada.');
        $invoice->load(['items', 'payments']);

        $data = $request->validate([
            'amount'    => ['required', 'numeric', 'min:0.01'],
            'method'    => ['required', Rule::in(['cash', 'card', 'transfer', 'wallet'])],
            'reference' => ['nullable', 'string', 'max:120'],
            'paid_at'   => ['nullable', 'date'],
        ]);

        $balance = $invoice->balance;
        if ($data['amount'] > $balance + 0.0001) {
            return back()->withErrors('El monto excede el saldo.')->withInput();
        }

        Payment::create([
            'invoice_id'  => $invoice->id,
            'amount'      => $data['amount'],
            'method'      => $data['method'],
            'reference'   => $data['reference'] ?? null,
            'paid_at'     => $data['paid_at'] ?? now(),
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
        if ($invoice->payments()->exists()) {
            return back()->withErrors('No se puede eliminar: la factura tiene pagos.');
        }
        $invoice->delete();
        return redirect()->route('admin.billing')->with('ok', 'Factura eliminada.');
    }

    public function createFromPlan(TreatmentPlan $plan)
    {
        $plan->load(['patient', 'treatments.service']);
        $items    = $plan->treatments;
        $subtotal = (float) $items->sum('price');
        $discount = 0.00;
        $taxPct   = 0.00;
        $tax      = round($subtotal * $taxPct / 100, 2);
        $grand    = max(0, round($subtotal - $discount + $tax, 2));

        return view('admin.billing.from_plan', compact('plan', 'items', 'subtotal', 'discount', 'taxPct', 'tax', 'grand'));
    }

    // Generar factura + items + (opcional) pago
    public function storeFromPlan(Request $request, \App\Models\TreatmentPlan $plan)
    {
        $data = $request->validate([
            'discount'    => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pay_now'     => ['nullable', 'boolean'],
            'amount'      => ['nullable', 'numeric', 'min:0'],
            'method'      => ['nullable', 'in:cash,card,transfer,wallet'],
            'reference'   => ['nullable', 'string', 'max:120'], // <-- NUEVO
            'notes'       => ['nullable', 'string'],
        ]);

        $plan->load(['patient', 'treatments.service']);
        $items    = $plan->treatments;
        $subtotal = (float) $items->sum('price');
        $discount = (float) ($data['discount']    ?? 0);
        $taxPct   = (float) ($data['tax_percent'] ?? 0);
        $tax      = round($subtotal * $taxPct / 100, 2);
        $grand    = max(0, round($subtotal - $discount + $tax, 2));

        return DB::transaction(function () use ($plan, $items, $subtotal, $discount, $taxPct, $tax, $grand, $data) {

            $inv = Invoice::create([
                'number'            => $this->nextNumber(),
                'patient_id'        => $plan->patient_id,
                'treatment_plan_id' => $plan->id,
                'status'            => 'issued',
                'discount'          => $discount,
                'tax_percent'       => $taxPct,
                'issued_at'         => now(),
                'notes'             => $data['notes'] ?? null,
                'created_by'        => optional(auth()->user())->id,
            ]);

            foreach ($items as $t) {
                $desc = $t->service?->name ?? 'Servicio';
                if ($t->tooth_code) $desc .= ' · Pieza ' . $t->tooth_code . ($t->surface ? ' ' . $t->surface : '');
                InvoiceItem::create([
                    'invoice_id'   => $inv->id,
                    'service_id'   => $t->service_id,
                    'treatment_id' => $t->id,
                    'description'  => $desc,
                    'quantity'     => 1,
                    'unit_price'   => $t->price,
                    'total'        => $t->price,
                ]);
            }

            if (!empty($data['pay_now'])) {
                $amount = min((float)($data['amount'] ?? 0), $grand);
                if ($amount > 0 && !empty($data['method'])) {
                    Payment::create([
                        'invoice_id'  => $inv->id,
                        'amount'      => $amount,
                        'method'      => $data['method'],             // enum válido
                        'reference'   => $data['reference'] ?? null,  // <-- FIX AQUÍ
                        'paid_at'     => now(),
                        'received_by' => optional(auth()->user())->id,
                    ]);
                }
            }

            $paidSum = (float) Payment::where('invoice_id', $inv->id)->sum('amount');
            if ($paidSum >= $grand && $grand > 0) {
                $inv->update(['status' => 'paid', 'paid_at' => now()]);
            }

            return redirect()->route('admin.invoices.show', $inv)->with('ok', 'Factura #' . $inv->number . ' creada.');
        });
    }

    private function nextNumber(): string
    {
        $year = date('Y');
        $last = Invoice::where('number', 'like', $year . '-%')
            ->orderBy('number', 'desc')
            ->value('number'); // ej. "2025-0042"

        $seq = 1;
        if ($last && preg_match('/^\d{4}\-(\d{4})$/', $last, $m)) {
            $seq = ((int)$m[1]) + 1;
        }
        return sprintf('%s-%04d', $year, $seq);
    }


    public function storePayment(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount'    => ['required', 'numeric', 'min:0.01'],
            'method'    => ['required', 'in:cash,card,transfer,wallet'],
            'reference' => ['nullable', 'string', 'max:120'],
        ]);

        // Crear pago
        Payment::create([
            'invoice_id'  => $invoice->id,
            'amount'      => $data['amount'],
            'method'      => $data['method'],
            'reference'   => $data['reference'] ?? null,
            'paid_at'     => now(),
            'received_by' => optional($request->user())->id,
        ]);

        // Recalcular totales con el nuevo pago
        $invoice->refresh()->load(['items', 'payments']);
        $tot = $this->computeTotals($invoice);

        // Si quedó saldada: cerrar, generar PDF y abrir en nueva pestaña
        if ($tot['grand'] > 0 && $tot['balance'] <= 0.0001) {
            $invoice->update(['status' => 'paid', 'paid_at' => now()]);
            $this->renderAndStorePdf($invoice); // guarda el PDF en storage/public/invoices/...

            return redirect()
                ->route('admin.invoices.show', $invoice)
                ->with('ok', 'Pago registrado. Factura saldada.')
                ->with('open_pdf', true); // <- la vista abre el PDF en nueva pestaña
        }

        // Si aún hay saldo: solo mensaje
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
                ->with('ok', 'Factura pagada. Comprobante listo.')
                ->with('open_pdf', true);
        }
        return back()->with('warn', 'Aún hay saldo pendiente; registra el pago primero.');
    }



    public function print(Invoice $invoice)
    {
        $invoice->load(['patient', 'items.service', 'payments']);
        $tot = $this->computeTotals($invoice);
        return view('admin.billing.print', array_merge(['invoice' => $invoice], $tot));
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['patient', 'items.service', 'payments']);
        $tot = $this->computeTotals($invoice);

        if (!class_exists(Pdf::class)) {
            return redirect()->route('admin.invoices.print', $invoice)
                ->with('warn', 'Instala barryvdh/laravel-dompdf para descargar PDF.');
        }

        $pdf = Pdf::loadView('admin.billing.print', array_merge(['invoice' => $invoice], $tot))
            ->setPaper('a4');

        $relPath = 'invoices/invoice_' . $invoice->number . '.pdf';
        Storage::disk('public')->put($relPath, $pdf->output()); // guarda comprobante

        // descarga inmediata
        return $pdf->download('factura_' . $invoice->number . '.pdf');
    }

    //metodos pdf

    public function view(Invoice $invoice)
    {
        $relPath = 'invoices/invoice_' . $invoice->number . '.pdf';
        $absPath = storage_path('app/public/' . $relPath);

        if (!Storage::disk('public')->exists($relPath)) {
            // no existe?? solo avisar
            return back()->with('warn', 'No existe el comprobante. Usa “Regenerar PDF”.');
        }

        return response()->file($absPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="factura_' . $invoice->number . '.pdf"',
            'Cache-Control'       => 'private, max-age=0, must-revalidate',
            'Pragma'              => 'public',
        ]);
    }

    public function download(Invoice $invoice)
    {
        $relPath = 'invoices/invoice_' . $invoice->number . '.pdf';
        if (!Storage::disk('public')->exists($relPath)) {
            return back()->with('warn', 'No existe el comprobante. Regénéralo.');
        }
        return response()->download(storage_path('app/public/' . $relPath), 'factura_' . $invoice->number . '.pdf');
    }

    public function regenerate(Invoice $invoice)
    {
        if (!class_exists(Pdf::class)) {
            return back()->with('warn', 'Instala barryvdh/laravel-dompdf para generar PDF.');
        }

        $invoice->load(['patient', 'items.service', 'payments']);
        $tot = $this->computeTotals($invoice);

        $pdf = Pdf::loadView('admin.billing.print', array_merge(['invoice' => $invoice], $tot))
            ->setPaper('a4');

        $relPath = 'invoices/invoice_' . $invoice->number . '.pdf';
        $absPath = storage_path('app/public/' . $relPath);

        Storage::disk('public')->put($relPath, $pdf->output()); // (re)genera y guarda

        // forzar descarga del archivo recien generado
        return response()->download($absPath, 'factura_' . $invoice->number . '.pdf');
    }

    private function computeTotals(Invoice $invoice): array
    {
        $subtotal = (float) $invoice->items()->sum('total');
        $tax      = round($subtotal * (float)$invoice->tax_percent / 100, 2);
        $grand    = max(0, round($subtotal - (float)$invoice->discount + $tax, 2));
        $paid     = (float) $invoice->payments()->sum('amount');
        $balance  = max(0, round($grand - $paid, 2));

        return compact('subtotal', 'tax', 'grand', 'paid', 'balance');
    }

    public function createFromAppointment(Appointment $appointment)
    {
        // Trae relaciones útiles para mostrar en la vista
        $appointment->load(['patient', 'service', 'dentist']);
        return view('admin.billing.create_from_appointment', compact('appointment'));
    }

    public function storeFromAppointment(Request $request, Appointment $appointment)
    {
        // Validación (igual que la tuya, sin patient_id)
        $data = $request->validate([
            'discount'     => ['nullable', 'numeric', 'min:0'],
            'tax_percent'  => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'        => ['nullable', 'string', 'max:500'],

            'items'                         => ['required', 'array', 'min:1'],
            'items.*.description'           => ['required', 'string', 'max:255'],
            'items.*.service_id'            => ['nullable', 'exists:services,id'],
            'items.*.treatment_id'          => ['nullable', 'exists:treatments,id'],
            'items.*.quantity'              => ['required', 'integer', 'min:1', 'max:9999'],
            'items.*.unit_price'            => ['required', 'numeric', 'min:0', 'max:9999999'],

            // Pago inmediato (opcional)
            'pay_amount'    => ['nullable', 'numeric', 'min:0'],
            'pay_method'    => ['nullable', 'in:cash,card,transfer,wallet'],
            'pay_reference' => ['nullable', 'string', 'max:120'],
        ]);

        $userId = optional($request->user())->id;

        DB::transaction(function () use (&$invoice, $data, $userId, $appointment, $request) {
            // === Número secuencial (FAC-000001, FAC-000002, ...) ===
            $last = \App\Models\Invoice::orderByDesc('id')->value('number');
            $nextSeq = 1;
            if ($last && preg_match('/(\d+)$/', $last, $m)) {
                $nextSeq = ((int)$m[1]) + 1;
            }
            $number = 'FAC-' . str_pad($nextSeq, 6, '0', STR_PAD_LEFT);

            // === Crear factura base ===
            $invoice = \App\Models\Invoice::create([
                'number'            => $number,
                'patient_id'        => $appointment->patient_id,
                'appointment_id'    => $appointment->id,
                'treatment_plan_id' => null,
                'status'            => 'issued',
                'discount'          => $data['discount'] ?? 0,
                'tax_percent'       => $data['tax_percent'] ?? 0,
                'issued_at'         => now(),
                'notes'             => $data['notes'] ?? null,
                'created_by'        => $userId,
            ]);

            // === Ítems ingresados manualmente (del formulario) ===
            $rows = [];
            $subtotal = 0.0;

            foreach ($data['items'] as $it) {
                $qty   = (int)   $it['quantity'];
                $unit  = (float) $it['unit_price'];
                $total = $qty * $unit;
                $subtotal += $total;

                $rows[] = [
                    'invoice_id'   => $invoice->id,
                    'service_id'   => $it['service_id']  ?? null,
                    'treatment_id' => $it['treatment_id'] ?? null,
                    'description'  => $it['description'],
                    'quantity'     => $qty,
                    'unit_price'   => $unit,
                    'total'        => $total,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }
            \App\Models\InvoiceItem::insert($rows);

            // === NUEVO: sumar insumos usados en la cita y agregarlos como 1 renglón ===
            $suppliesTotal = \App\Models\AppointmentSupply::where('appointment_id', $appointment->id)
                ->selectRaw('COALESCE(SUM(qty * COALESCE(unit_cost_at_issue,0)),0) as total')
                ->value('total');

            if ($suppliesTotal > 0) {
                \App\Models\InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'service_id'  => null,
                    'treatment_id' => null,
                    'description' => 'Insumos utilizados (cita #' . $appointment->id . ')',
                    'quantity'    => 1,
                    'unit_price'  => $suppliesTotal,
                    'total'       => $suppliesTotal,
                ]);

                // El subtotal de la factura debe incluir los insumos
                $subtotal += (float)$suppliesTotal;
            }

            // === Totales (base / impuesto / total) usando el subtotal actualizado ===
            $discount   = (float)($data['discount'] ?? 0);
            $taxPercent = (float)($data['tax_percent'] ?? 0);
            $base       = max($subtotal - $discount, 0);
            $grandTotal = $base + ($base * $taxPercent / 100);

            // === Pago inmediato opcional ===
            $amount = (float)($data['pay_amount'] ?? 0);
            $method = $data['pay_method'] ?? null;

            if ($amount > 0 && $method) {
                \App\Models\Payment::create([
                    'invoice_id'  => $invoice->id,
                    'amount'      => $amount,
                    'method'      => $method, // 'cash','card','transfer','wallet'
                    'reference'   => $request->input('pay_reference'),
                    'paid_at'     => now(),
                    'received_by' => $userId,
                ]);

                // Si cubre el total => marcar pagada
                if ($amount + 0.0001 >= $grandTotal) {
                    $invoice->update([
                        'status'  => 'paid',
                        'paid_at' => now(),
                    ]);
                }
            }
        });

        // Redirigir a la factura
        return redirect()->route('admin.invoices.show', $invoice)->with('ok', 'Factura creada.');
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

            $relPath = 'invoices/invoice_' . $invoice->number . '.pdf';
            Storage::disk('public')->put($relPath, $pdf->output());
        }
    }
}
