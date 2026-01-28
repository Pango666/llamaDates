<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClinicalNote;
use App\Models\Consent;
use App\Models\Dentist;
use App\Models\Diagnosis;
use App\Models\Invoice;
use App\Models\Odontogram;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\TreatmentPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\MedicalHistory;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    /** Listado + filtro simple */
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        $status = $request->get('status', 'active'); // active, inactive, all

        $patients = Patient::query()
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name',  'like', "%{$q}%")
                        ->orWhere('email',      'like', "%{$q}%")
                        ->orWhere('phone',      'like', "%{$q}%")
                        ->orWhere('ci',         'like', "%{$q}%");
                });
            })
            ->when($status === 'active', fn($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();

        // Métricas rápidas
        $counts = [
            'total'    => Patient::count(),
            'active'   => Patient::where('is_active', true)->count(),
            'inactive' => Patient::where('is_active', false)->count(),
        ];

        return view('admin.patients.index', [
            'patients' => $patients,
            'q'        => $q,
            'status'   => $status,
            'counts'   => $counts,
        ]);
    }

    /** Form crear */
    public function create(Request $request)
    {
        // para el formulario vacío
        $patient = new Patient(['is_active' => true]);
        return view('admin.patients.create', compact('patient'));
    }

    /** Guardar */
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'ci'         => ['required', 'unique:patients,ci', 'string', 'max:50'],
            'birthdate'  => ['nullable', 'date'],
            'email'      => ['required', 'email', 'max:150', 'unique:patients,email'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'address'    => ['nullable', 'string'],

            // NO nuevos endpoints: flags del mismo form
            'create_portal_user' => ['nullable', 'boolean'],
        ]);
        
        $data['is_active'] = true;

        $patient = Patient::create($data);

        // Guardar información médica si se proporcionó
        $this->saveMedicalHistory($request, $patient);

        // Crear usuario portal (opcional) SIN rutas nuevas
        if ($request->boolean('create_portal_user') && !empty($data['email'])) {
            // Evita colisión con tabla users
            $request->validate([
                'email' => ['email', 'max:150', 'unique:users,email'],
            ]);

            $plain = $request->ci;

            $user = User::create([
                'name'     => trim($patient->first_name . ' ' . $patient->last_name),
                'email'    => $data['email'],
                'password' => Hash::make($plain),
                'role'     => 'paciente',
                'status'   => 'active',
            ]);

            // asigna rol "paciente" en la tabla pivot role_user
            $rolePaciente = \App\Models\Role::where('name', 'paciente')->first();
            if ($rolePaciente) {
                \DB::table('role_user')->updateOrInsert(
                    ['user_id' => $user->id, 'role_id' => $rolePaciente->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }

            // relación
            $patient->update(['user_id' => $user->id]);

            // --- EMAIL: Welcome ---
            try {
                \Illuminate\Support\Facades\Mail::to($user)->send(new \App\Mail\WelcomeUser($user, $plain));
                \App\Models\EmailLog::create([
                    'to' => $user->email,
                    'subject' => 'Bienvenido a DentalCare',
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } catch (\Exception $e) {
                 \App\Models\EmailLog::create([
                    'to' => $user->email,
                    'subject' => 'Bienvenido a DentalCare',
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
            }

            return redirect()
                ->route('admin.patients.show', $patient)
                ->with('ok', 'Paciente creado y usuario de portal habilitado.')
                ->with('portal_password', $plain);
        }

        return redirect()
            ->route('admin.patients.show', $patient)
            ->with('ok', 'Paciente creado correctamente.');
    }

    /** Perfil */
    public function show(Patient $patient)
    {
        // últimas 10 citas del paciente
        $appointments = Appointment::with(['service:id,name', 'dentist:id,name'])
            ->where('patient_id', $patient->id)
            ->orderBy('date', 'desc')->orderBy('start_time', 'desc')
            ->limit(10)
            ->get();

        // edad (si tiene fecha)
        $age = null;
        if ($patient->birthdate) {
            $age = Carbon::parse($patient->birthdate)->age;
        }

        $plans = TreatmentPlan::where('patient_id', $patient->id)
            ->withCount('treatments')
            ->orderByDesc('created_at')
            ->limit(3)->get();

        return view('admin.patients.show', compact('patient', 'age', 'appointments', 'plans'));
    }

    /** Form editar */
    public function edit(Patient $patient)
    {
        return view('admin.patients.edit', compact('patient'));
    }

    /** Actualizar */
    public function update(Request $request, Patient $patient)
    {
        // 1) Actualizar datos del paciente (igual que ya tenías)
        $data = $request->validate([
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name'  => ['nullable', 'string', 'max:100'],
            'ci'         => ['nullable', 'string', 'max:50'],
            'birthdate'  => ['nullable', 'date'],
            'email'      => ['nullable', 'email', 'max:150', Rule::unique('patients', 'email')->ignore($patient->id)],
            'phone'      => ['nullable', 'string', 'max:50'],
            'address'    => ['nullable', 'string'],
        ]);

        $patient->update($data);

        // 2) Guardar información médica si se proporcionó
        $this->saveMedicalHistory($request, $patient);

        // 3) Acciones de portal (opcional)
        if ($request->has('portal_action') && $patient->user) {
            $action = $request->input('portal_action');
            $user   = $patient->user;
            
            if ($action === 'disable' && $user->status === 'active') {
                $user->update(['status' => 'suspended']);
                
                // Email
                 try {
                    \Illuminate\Support\Facades\Mail::to($user)->send(new \App\Mail\AccountSuspended($user));
                    \App\Models\EmailLog::create([
                        'to' => $user->email,
                        'subject' => 'Aviso de Suspensión de Cuenta - DentalCare',
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                } catch (\Exception $e) {
                     \App\Models\EmailLog::create([
                        'to' => $user->email,
                        'subject' => 'Aviso de Suspensión de Cuenta - DentalCare',
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                    ]);
                }

            } elseif ($action === 'enable' && $user->status !== 'active') {
                $user->update(['status' => 'active']);
                
                // Email
                try {
                     \Illuminate\Support\Facades\Mail::to($user)->send(new \App\Mail\AccountReactivated($user));
                    \App\Models\EmailLog::create([
                        'to' => $user->email,
                        'subject' => 'Tu Cuenta ha sido Reactivada - DentalCare',
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                } catch (\Exception $e) {
                     \App\Models\EmailLog::create([
                        'to' => $user->email,
                        'subject' => 'Tu Cuenta ha sido Reactivada - DentalCare',
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
        
        return redirect()
            ->route('admin.patients.show', $patient)
            ->with('ok', 'Paciente actualizado.');
    }

    /** Toggle Active Status (Replaces Delete) */
    public function toggle(Patient $patient)
    {
        $newState = !$patient->is_active;
        $patient->update(['is_active' => $newState]);

        // Sincronizar usuario si existe
        if ($patient->user) {
            $patient->user->update([
                'status' => $newState ? 'active' : 'suspended'
            ]);

            // --- EMAIL: Account Suspended / Reactivated ---
            if ($patient->user->wasChanged('status')) {
                 $user = $patient->user;
                 if ($user->status === 'suspended') {
                    try {
                        \Illuminate\Support\Facades\Mail::to($user)->send(new \App\Mail\AccountSuspended($user));
                        \App\Models\EmailLog::create([
                            'to' => $user->email,
                            'subject' => 'Aviso de Suspensión de Cuenta - DentalCare',
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                         \App\Models\EmailLog::create([
                            'to' => $user->email,
                            'subject' => 'Aviso de Suspensión de Cuenta - DentalCare',
                            'status' => 'failed',
                            'error' => $e->getMessage(),
                        ]);
                    }
                 } elseif ($user->status === 'active') {
                    try {
                         \Illuminate\Support\Facades\Mail::to($user)->send(new \App\Mail\AccountReactivated($user));
                        \App\Models\EmailLog::create([
                            'to' => $user->email,
                            'subject' => 'Tu Cuenta ha sido Reactivada - DentalCare',
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                         \App\Models\EmailLog::create([
                            'to' => $user->email,
                            'subject' => 'Tu Cuenta ha sido Reactivada - DentalCare',
                            'status' => 'failed',
                            'error' => $e->getMessage(),
                        ]);
                    }
                 }
            }
        }

        $verb = $newState ? 'activado' : 'desactivado';
        return back()->with('ok', "Paciente $verb correctamente.");
    }

    public function destroy(Patient $patient)
    {
        // Mantengo destroy por si acaso pero redirige a toggle o error?
        // Mejor lo dejo por si alguna ruta vieja lo llama, pero hago soft logic si se prefiere.
        // El usuario pidió REEMPLAZAR.
        return $this->toggle($patient);
    }


    ///PANEL DEL PACIENTE Y OPERACIONES
    public function dashboard()
    {
        $pid = $this->currentPatientId();
        abort_if(!$pid, 403, 'No se encontró el paciente vinculado.');

        $nextAppointments = Appointment::with(['service:id,name', 'dentist:id,name'])
            ->where('patient_id', $pid)
            ->whereIn('status', ['reserved', 'confirmed', 'in_service'])
            ->where(function ($w) {
                $now = now()->format('H:i:s');
                $w->whereDate('date', '>', today())
                    ->orWhere(function ($w2) use ($now) {
                        $w2->whereDate('date', today())
                            ->where('end_time', '>=', $now);
                    });
            })
            ->orderBy('date')->orderBy('start_time')
            ->take(5)->get();

        $lastInvoices = Invoice::withCount('items')
            ->where('patient_id', $pid)
            ->orderByDesc('created_at')
            ->take(5)->get();

        return view('patient.dashboard', compact('nextAppointments', 'lastInvoices'));
    }

    /* =======================
     * LISTADO DE CITAS (TABS)
     * ======================= */
    public function appointmentsIndex(Request $request)
    {
        $pid = $this->currentPatientId();
        abort_if(!$pid, 403);

        $tab = $request->get('tab', 'programadas');

        $q = Appointment::with(['service:id,name', 'dentist:id,name'])
            ->where('patient_id', $pid)
            ->orderByDesc('date')
            ->orderByDesc('start_time');

        if ($tab === 'programadas') {
            $q->whereIn('status', ['reserved', 'confirmed', 'in_service']);
        } elseif ($tab === 'asistidas') {
            $q->whereIn('status', ['done', 'completed']);
        } elseif ($tab === 'no_asistio') {
            $q->whereIn('status', ['no_show', 'non-attendance', 'non_attendance']);
        } elseif ($tab === 'canceladas') {
            $q->whereIn('status', ['canceled', 'cancelled']);
        } elseif ($tab === 'todas') {
            // sin filtro
        } else {
            $tab = 'programadas';
            $q->whereIn('status', ['reserved', 'confirmed', 'in_service']);
        }

        $appointments = $q->paginate(10)->withQueryString();

        return view('patient.appointments.index', compact('appointments', 'tab'));
    }


    /* =======================
     * CREAR CITA (PACIENTE)
     * ======================= */
    public function appointmentsCreate()
    {
        $pid = $this->currentPatientId();
        abort_if(!$pid, 403);

        $services = Service::where('active', true)->orderBy('name')->get(['id', 'name', 'price', 'duration_min']);
        $dentists = Dentist::where('status', 1)
            ->where(function($q) {
                 $q->whereDoesntHave('user')
                   ->orWhereHas('user', fn($u) => $u->where('status', 'active'));
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('patient.appointments.create', compact('services', 'dentists'));
    }

    public function appointmentsShow(Appointment $appointment)
    {
        $pid = $this->currentPatientId();
        abort_if(!$pid, 403);
        abort_if((int)$appointment->patient_id !== (int)$pid, 403);

        $appointment->load([
            'service:id,name',
            'dentist:id,name',
            'attachments:id,appointment_id,original_name,path,created_at',
        ]);

        return view('patient.appointments.show', compact('appointment'));
    }

    public function availability(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'date'       => ['required', 'date'],
            'dentist_id' => ['nullable', 'exists:dentists,id'],
        ]);

        $svc  = Service::findOrFail($data['service_id']);
        $date = Carbon::parse($data['date']);
        $dow  = $date->dayOfWeek;

        $dentists = Dentist::query()
            ->where('status', 1)
            ->where(function($q) {
                 $q->whereDoesntHave('user')
                   ->orWhereHas('user', fn($u) => $u->where('status', 'active'));
            })
            ->when(!empty($data['dentist_id']), fn($q) => $q->where('id', $data['dentist_id']))
            ->get(['id', 'name']);

        $duration = max(1, (int)($svc->duration_min ?? 30));
        $slots = [];

        foreach ($dentists as $d) {
            $blocks = Schedule::where('dentist_id', $d->id)
                ->where('day_of_week', $dow)
                ->orderBy('start_time')
                ->get(['start_time', 'end_time', 'chair_id']);

            foreach ($blocks as $b) {
                $start = Carbon::parse($date->toDateString() . ' ' . $b->start_time);
                $end   = Carbon::parse($date->toDateString() . ' ' . $b->end_time);

                $t = $start->copy();
                while ($t->copy()->addMinutes($duration)->lte($end)) {
                    $slotStart = $t->copy();
                    $slotEnd   = $t->copy()->addMinutes($duration);

                    $conflict = Appointment::where('dentist_id', $d->id)
                        ->where('is_active', true)
                        ->whereDate('date', $date->toDateString())
                        ->where('start_time', '<', $slotEnd->format('H:i:s'))
                        ->where('end_time',   '>', $slotStart->format('H:i:s'))
                        ->exists();

                    if (!$conflict) {
                        $slots[] = [
                            'dentist_id' => $d->id,
                            'dentist'    => $d->name,
                            'time'       => $slotStart->format('H:i'),
                        ];
                    }

                    $t->addMinutes($duration);
                }
            }
        }

        usort($slots, fn($a, $b) => strcmp($a['time'], $b['time']));

        return response()->json([
            'slots'        => $slots,
            'duration_min' => $duration,
        ]);
    }


    /** Guardar cita (misma lógica base del admin, pero desde el portal) */
    public function appointmentsStore(Request $r)
    {
        $pid = $this->currentPatientId();
        abort_if(!$pid, 403);

        $data = $r->validate([
            'service_id' => 'required|exists:services,id',
            'dentist_id' => 'required|exists:dentists,id',
            'date'       => 'required|date',
            'start_time' => 'required', // "HH:MM" u "HH:MM:SS"
            'notes'      => 'nullable|string',
        ]);

        // Normaliza hora
        $startStr = strlen($data['start_time']) === 5 ? $data['start_time'] . ':00' : $data['start_time'];

        $svc   = Service::findOrFail($data['service_id']);
        $date  = Carbon::parse($data['date']);
        $start = Carbon::parse($date->toDateString() . ' ' . $startStr);
        $end   = $start->copy()->addMinutes($svc->duration_min);

        if ($start->isPast()) {
            return back()->withErrors(['start_time' => 'No se puede reservar en el pasado'])->withInput();
        }

        // Conflicto activo
        $conflict = Appointment::where('dentist_id', $data['dentist_id'])
            ->whereDate('date', $date->toDateString())->where('is_active', true)
            ->where('start_time', '<', $end->format('H:i:s'))
            ->where('end_time',   '>', $start->format('H:i:s'))
            ->exists();

        if ($conflict) {
            return back()->withErrors(['start_time' => 'Horario no disponible'])->withInput();
        }

        // Determinar silla por bloque
        $dow = $date->dayOfWeek;
        $block = Schedule::where('dentist_id', $data['dentist_id'])
            ->where('day_of_week', $dow)
            ->where('start_time', '<=', $start->format('H:i:s'))
            ->where('end_time',   '>=', $end->format('H:i:s'))
            ->orderBy('start_time', 'desc')
            ->first();

        if (!$block) {
            return back()->withErrors(['start_time' => 'El horario seleccionado no pertenece al turno del odontólogo.'])->withInput();
        }

        $chairId = $block->chair_id ?? Dentist::whereKey($data['dentist_id'])->value('chair_id');
        if (!$chairId) {
            return back()->withErrors(['start_time' => 'No hay silla asignada para ese turno.'])->withInput();
        }

        $appointment = Appointment::create([
            'patient_id' => $pid,
            'dentist_id' => $data['dentist_id'],
            'service_id' => $data['service_id'],
            'chair_id'   => $chairId,
            'date'       => $date->toDateString(),
            'start_time' => $start->format('H:i:s'),
            'end_time'   => $end->format('H:i:s'),
            'status'     => 'reserved',
            'is_active'  => true,
            'notes'      => $data['notes'] ?? null,
        ]);

        // --- EMAIL: Confirmation ---
        try {
            $patient = Patient::find($pid);
            if ($patient && $patient->email) {
                \Illuminate\Support\Facades\Mail::to($patient->email)
                    ->send(new \App\Mail\AppointmentConfirmation($appointment));

                \App\Models\EmailLog::create([
                    'to' => $patient->email,
                    'subject' => 'Confirmación de Cita - DentalCare',
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
             $patient = Patient::find($pid);
             if ($patient && $patient->email) {
                \App\Models\EmailLog::create([
                    'to' => $patient->email,
                    'subject' => 'Confirmación de Cita - DentalCare',
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
             }
        }

        return redirect()->route('app.appointments.index')->with('ok', 'Cita reservada.');
    }

    /** Cancelar (solo reserved/confirmed y futura) */
    public function appointmentsCancel(Request $request, Appointment $appointment)
    {
        $pid = $this->currentPatientId();
        abort_if(!$pid, 403);
        abort_if((int)$appointment->patient_id !== (int)$pid, 403, 'No puedes cancelar esta cita.');

        $startAt = $this->startsAt($appointment);
        $cancelable = in_array($appointment->status, ['reserved', 'confirmed'], true) && now()->lt($startAt);

        if (!$cancelable) {
            return back()->with('warn', 'La cita no se puede cancelar en este estado.');
        }

        $appointment->update([
            'status'          => 'canceled',
            'is_active'       => false,
            'canceled_at'     => now(),
            'canceled_reason' => $request->input('reason'),
        ]);

        return back()->with('ok', 'Cita cancelada.');
    }

    public function appointmentsConfirm(Request $request, Appointment $appointment)
    {
        $pid = $this->currentPatientId();
        abort_if(!$pid, 403);
        abort_if((int)$appointment->patient_id !== (int)$pid, 403, 'No puedes confirmar esta cita.');

        $startAt = $this->startsAt($appointment);

        $confirmable = ($appointment->status === 'reserved') && now()->lt($startAt);
        if (!$confirmable) {
            return back()->with('warn', 'La cita no se puede confirmar en este estado.');
        }

        $appointment->update([
            'status'    => 'confirmed',
            'is_active' => true,
        ]);

        return back()->with('ok', 'Cita confirmada.');
    }

    /* =======================
     * FACTURAS (opcional)
     * ======================= */
    public function invoicesIndex()
    {
        $pid = $this->currentPatientId();
        abort_if(!$pid, 403);

        $invoices = Invoice::withCount('items')
            ->where('patient_id', $pid)
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('patient.invoices.index', compact('invoices'));
    }

    public function invoicesShow(Invoice $invoice)
    {
        $pid = $this->currentPatientId();
        abort_if(!$pid, 403);
        abort_if((int) $invoice->patient_id !== (int) $pid, 403);

        $invoice->load([
            'items.service',
            'payments',
            'appointment',
        ]);

        $subtotal = (float) $invoice->items->sum('total');
        $discount = (float) ($invoice->discount ?? 0);

        $taxPercent = (float) ($invoice->tax_percent ?? 0);
        $taxBase = max(0, $subtotal - $discount);
        $tax = round($taxBase * ($taxPercent / 100), 2);

        $grand = round($taxBase + $tax, 2);

        // Ajusta el campo si en tu Payment se llama distinto:
        $paid = (float) $invoice->payments->sum('amount');

        $balance = round(max(0, $grand - $paid), 2);

        $isPaid = $balance <= 0 || $invoice->status === 'paid';

        return view('patient.invoices.show', compact(
            'invoice',
            'subtotal',
            'discount',
            'taxPercent',
            'tax',
            'grand',
            'paid',
            'balance',
            'isPaid'
        ));
    }

    private function currentPatientId(): ?int
    {
        $u = auth()->user();
        if (!$u) return null;

        if (isset($u->patient_id) && $u->patient_id) {
            return (int) $u->patient_id;
        }
        // fallback por si hay relación user_id en patients
        return Patient::where('user_id', $u->id)->value('id');
    }

    /** Construye Carbon de inicio sin concatenar cadenas inválidas */
    private function startsAt(Appointment $a): Carbon
    {
        $h = strlen($a->start_time) === 5 ? $a->start_time . ':00' : $a->start_time; // HH:MM:SS
        return Carbon::parse($a->date)->setTimeFromTimeString($h);
    }

    public function profile(Request $request)
    {
        $pid = $this->currentPatientId();
        abort_if(!$pid, 403);

        $tab = $request->get('tab', 'datos');

        $user    = $request->user();
        $patient = \App\Models\Patient::find($pid);

        $notes = collect();
        $diagnoses = collect();

        if ($tab === 'historia') {
            $notes = \App\Models\ClinicalNote::with([
                'author:id,name',
                'appointment:id,date,start_time,end_time,service_id,dentist_id'
            ])->whereHas('appointment', fn($q) => $q->where('patient_id', $pid))
                ->orderByDesc('created_at')->limit(50)->get();

            $diagnoses = \App\Models\Diagnosis::with([
                'appointment:id,date,start_time,end_time,service_id,dentist_id'
            ])->whereHas('appointment', fn($q) => $q->where('patient_id', $pid))
                ->orderByDesc('created_at')->limit(100)->get();
        }

        $serviceNames = \App\Models\Service::pluck('name', 'id');
        $dentistNames = \App\Models\Dentist::pluck('name', 'id');

        // Odontograma actual (si existe alguno)
        $currentOdo = Odontogram::where('patient_id', $pid)->latest('created_at')->withCount('teeth')->first();

        return view('patient.profile', compact('tab', 'user', 'patient', 'notes', 'diagnoses', 'serviceNames', 'dentistNames', 'currentOdo'));
    }

    /** Vista readonly del odontograma actual */
    public function odontogram(Request $request)
    {
        $pid = $this->currentPatientId();
        abort_if(!$pid, 403);

        $odo = Odontogram::where('patient_id', $pid)
            ->latest('created_at')
            ->with(['teeth' => function ($q) {
                $q->orderBy('tooth_code');
            }])
            ->first();

        return view('patient.odontogram', compact('odo'));
    }

    /** Actualiza datos básicos del usuario (solo nombre, no toco otras columnas) */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            // agrega más campos aquí si existen en tu esquema
        ]);

        $u = $request->user();
        $u->update([
            'name' => $request->input('name'),
        ]);

        return back()->with('ok', 'Perfil actualizado.');
    }

    /** Cambiar contraseña */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $u = $request->user();
        if (!Hash::check($request->input('current_password'), $u->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
        }
        $u->password = Hash::make($request->input('password'));
        $u->save();

        return back()->with('ok', 'Contraseña actualizada.');
    }

    public function findByCI($ci)
    {
        $p = Patient::where('ci', $ci)->first();

        if (!$p) {
            return response()->json(['exists' => false]);
        }

        return response()->json([
            'exists' => true,
            'patient' => [
                'id'         => $p->id,
                'first_name' => $p->first_name,
                'last_name'  => $p->last_name,
                'phone'      => $p->phone,
            ]
        ]);
    }

    /**
     * Guarda o actualiza la información médica del paciente
     */
    private function saveMedicalHistory(Request $request, Patient $patient): void
    {
        // Verificar si hay algún campo médico proporcionado
        $medicalFields = ['allergies', 'medications', 'systemic_diseases', 'surgical_history', 'habits', 'smoker', 'pregnant'];
        $hasMedicalData = false;
        
        foreach ($medicalFields as $field) {
            if ($request->filled($field) || $request->has($field)) {
                $hasMedicalData = true;
                break;
            }
        }
        
        if (!$hasMedicalData) {
            return;
        }

        // Crear o actualizar el registro de historia médica
        MedicalHistory::updateOrCreate(
            ['patient_id' => $patient->id],
            [
                'allergies'         => $request->input('allergies'),
                'medications'       => $request->input('medications'),
                'systemic_diseases' => $request->input('systemic_diseases'),
                'surgical_history'  => $request->input('surgical_history'),
                'habits'            => $request->input('habits'),
                'smoker'            => $request->boolean('smoker'),
                'pregnant'          => $request->has('pregnant') ? $request->boolean('pregnant') : null,
            ]
        );
    }
}
