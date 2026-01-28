<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Attachment;
use App\Models\Chair;
use App\Models\ClinicalNote;
use App\Models\Dentist;
use App\Models\Diagnosis;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class AppointmentController extends Controller
{
    public function dashboard(Request $request)
    {
        $today = Carbon::today();
        $month = Carbon::parse($request->get('month', $today->format('Y-m'))); // YYYY-MM
        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        // Detectar si es odontólogo
        $user = auth()->user();
        $dentistId = $user->dentist ? $user->dentist->id : null;

        // Stats query base
        $visitsQuery = Appointment::whereDate('date', $today);
        if ($dentistId) {
            $visitsQuery->where('dentist_id', $dentistId);
        }

        $stats = [
            'patients'    => Patient::count(),
            'dentists'    => Dentist::count(),
            'services'    => Service::count(),
            'todayVisits' => $visitsQuery->count(),
        ];

        $day = Carbon::parse($request->get('day', $today->toDateString()));

        // Appointments query
        $apptQuery = Appointment::with(['patient:id,first_name,last_name', 'service:id,name'])
            ->whereDate('date', $day)
            ->orderBy('start_time');

        if ($dentistId) {
            $apptQuery->where('dentist_id', $dentistId);
        }
        $appointments = $apptQuery->get();

        // Calendar counts query
        $perDayQuery = Appointment::selectRaw('date, COUNT(*) as total')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('date');

        if ($dentistId) {
            $perDayQuery->where('dentist_id', $dentistId);
        }
        $perDay = $perDayQuery->pluck('total', 'date');

        return view('admin.dashboard', compact('stats', 'month', 'day', 'appointments', 'perDay'));
    }

    // AJAX para refrescar calendario y la lista del día
    public function dashboardData(Request $request)
    {
        $month = Carbon::parse($request->get('month', now()->format('Y-m')));
        $day   = Carbon::parse($request->get('day',   now()->toDateString()));

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        // Detectar si es odontólogo
        $user = auth()->user();
        $dentistId = $user->dentist ? $user->dentist->id : null;

        // Appointments query
        $apptQuery = Appointment::with(['patient:id,first_name,last_name', 'service:id,name'])
            ->whereDate('date', $day)
            ->orderBy('start_time');

        if ($dentistId) {
            $apptQuery->where('dentist_id', $dentistId);
        }
        $appointments = $apptQuery->get();

        // Calendar counts query
        $perDayQuery = Appointment::selectRaw('date, COUNT(*) as total')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('date');

        if ($dentistId) {
            $perDayQuery->where('dentist_id', $dentistId);
        }
        $perDay = $perDayQuery->pluck('total', 'date');

        $calendarHtml = View::make('admin.partials._calendar', compact('month', 'day', 'perDay'))->render();
        $listHtml     = View::make('admin.partials._day_list', compact('day', 'appointments'))->render();

        return response()->json([
            'calendar'    => $calendarHtml,
            'day_list'    => $listHtml,
            'month_label' => $month->translatedFormat('F Y'),
        ]);
    }

    public function adminIndex(Request $r)
    {
        // Si el usuario NO manda date, usamos hoy.
        $date = $r->filled('date') ? $r->date : now()->toDateString();

        // Base (para métricas y lista)
        $base = Appointment::query()
            ->with([
                'patient:id,first_name,last_name,phone',
                'service:id,name',
                'dentist:id,name',
            ])
            ->whereDate('date', $date)
            ->orderBy('start_time');

        // FORCE FILTER if dentist
        $user = auth()->user();
        if ($user->dentist) {
             $r->merge(['dentist_id' => $user->dentist->id]);
        }

        // Odontólogo
        if ($r->filled('dentist_id')) {
            $base->where('dentist_id', $r->dentist_id);
        }

        // Buscador (paciente / servicio / odontólogo / teléfono)
        if ($r->filled('q')) {
            $q = trim(mb_strtolower($r->q));
            $base->where(function ($qq) use ($q) {
                $qq->whereHas('patient', function ($p) use ($q) {
                    $p->whereRaw('LOWER(first_name) LIKE ?', ["%{$q}%"])
                      ->orWhereRaw('LOWER(last_name) LIKE ?',  ["%{$q}%"])
                      ->orWhereRaw('LOWER(phone) LIKE ?',      ["%{$q}%"]);
                })->orWhereHas('service', function ($s) use ($q) {
                    $s->whereRaw('LOWER(name) LIKE ?', ["%{$q}%"]);
                })->orWhereHas('dentist', function ($d) use ($q) {
                    $d->whereRaw('LOWER(name) LIKE ?', ["%{$q}%"]);
                });
            });
        }

        $counts = (clone $base)
            ->reorder() // <- quita cualquier ORDER BY heredado
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $inasistencias = (int)($counts['no_show'] ?? 0) + (int)($counts['non-attendance'] ?? 0);

        $statusCounts = [
            'reserved'      => (int)($counts['reserved'] ?? 0),
            'confirmed'     => (int)($counts['confirmed'] ?? 0),
            'in_service'    => (int)($counts['in_service'] ?? 0),
            'done'          => (int)($counts['done'] ?? 0),
            'no_show'       => $inasistencias,
            'canceled'      => (int)($counts['canceled'] ?? 0),
        ];

        
        $list = clone $base;

        if ($r->filled('status')) {
            if ($r->status === 'no_show') {
                $list->whereIn('status', ['no_show', 'non-attendance']);
            } else {
                $list->where('status', $r->status);
            }
        }

        return view('admin.appointments.index', [
            'appointments' => $list->paginate(20)->withQueryString(),
            'dentists'     => Dentist::orderBy('name')->get(['id', 'name']),
            'services'     => Service::where('active', true)->orderBy('name')->get(['id', 'name', 'duration_min']),
            'filters'      => [
                'date'      => $date, 
                'dentist_id'=> $r->input('dentist_id'),
                'status'    => $r->input('status'),
                'q'         => $r->input('q'),
            ],
            'statusCounts' => $statusCounts,
        ]);
    }

    // /admin/citas/nueva  → formulario
    public function createForm(Request $request)
    {
        $patients = Patient::where('is_active', true)->orderBy('last_name')->get();
        // Solo odontólogos activos Y cuyo usuario (si tiene) esté activo
        $dentists = Dentist::where('status', 1)
            ->where(function($q) {
                 $q->whereDoesntHave('user')
                   ->orWhereHas('user', fn($u) => $u->where('status', 'active'));
            })
            ->orderBy('name')
            ->get();
        $services = Service::orderBy('name')->where('active',true)->get();

        $prefill = [
            'patient_id' => $request->query('patient_id'),
            'dentist_id' => $request->query('dentist_id'),
            'service_id' => $request->query('service_id'),
            'date'       => $request->query('date'),
            'notes'      => $request->query('notes'),
        ];

        return view('admin.appointments.create', compact('patients', 'dentists', 'services', 'prefill'));
    }


    public function show(Appointment $appointment)
    {
        $appointment->load(['patient.medicalHistory', 'dentist', 'service']);

        // Trae la última factura vinculada a esta cita (si hubiera)
        $invoice = \App\Models\Invoice::with(['items', 'payments'])
            ->where('appointment_id', $appointment->id)
            ->latest()
            ->first();

        $totals = null;
        if ($invoice) {
            $subtotal = $invoice->items->sum('total');
            $discount = (float) $invoice->discount;
            $taxPct   = (float) $invoice->tax_percent;

            $base  = max($subtotal - $discount, 0);
            $grand = $base + ($base * $taxPct / 100);

            $paid = $invoice->payments->sum('amount');
            $due  = max($grand - $paid, 0);

            $totals = compact('subtotal', 'base', 'grand', 'paid', 'due');
        }

        return view('admin.appointments.show', compact('appointment', 'invoice', 'totals'));
    }

    // GET /admin/citas/disponibilidad?dentist_id=&service_id=&date=
    // (AJAX para el formulario – dejamos JSON aquí porque es para la vista)
    public function availability(Request $r)
    {
        $r->validate([
            'dentist_id' => 'required|exists:dentists,id',
            'service_id' => 'required|exists:services,id',
            'date'       => 'required|date',
        ]);

        $tz   = config('app.timezone', 'America/La_Paz');
        $now  = now($tz);
        $date = \Carbon\Carbon::parse($r->date, $tz)->startOfDay();

        //Nada para fechas PASADAS
        if ($date->lt($now->copy()->startOfDay())) {
            \Log::debug('availability: date in the past', ['date' => $date->toDateString(), 'now' => $now->toDateString()]);
            return response()->json([]);
        }

        $service = \App\Models\Service::findOrFail((int)$r->service_id);
        $duration = (int) ($service->duration_min ?? 30);
        if ($duration <= 0) $duration = 30;

        // DEBUG: si pides ?debug_slots=1 devolvemos slots “de mentira” para validar UI
        if ($r->boolean('debug_slots')) {
            $base = \Carbon\Carbon::parse($date->toDateString() . ' 09:00:00', $tz);
            $end  = \Carbon\Carbon::parse($date->toDateString() . ' 18:00:00', $tz);
            $fake = [];
            $cur  = $base->copy();
            while ($cur->copy()->addMinutes($duration)->lte($end)) {
                if (!($date->isSameDay($now) && $cur->lte($now))) {
                    $fake[] = $cur->format('H:i');
                }
                $cur->addMinutes($duration);
            }
            \Log::debug('availability DEBUG SLOTS', compact('fake', 'duration'));
            return response()->json($fake);
        }

        $dayOfWeek = $date->dayOfWeek; // 0=Dom .. 6=Sáb
        $scheds = \App\Models\Schedule::where('dentist_id', (int)$r->dentist_id)
            ->where('day_of_week', $dayOfWeek)
            ->get();

        // Cargas ocupadas del día
        $busy = Appointment::where('dentist_id', (int)$r->dentist_id)
            ->whereDate('date', $date)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhere('status', '!=', 'canceled');
            })
            ->get(['start_time', 'end_time'])
            ->map(fn($a) => [
                'start' => \Carbon\Carbon::parse($date->toDateString() . ' ' . $a->start_time, $tz),
                'end'   => \Carbon\Carbon::parse($date->toDateString() . ' ' . $a->end_time,   $tz),
            ]);

        \Log::debug('availability INPUT', [
            'dentist_id' => (int)$r->dentist_id,
            'service_id' => (int)$r->service_id,
            'date'       => $date->toDateString(),
            'dayOfWeek'  => $dayOfWeek,
            'duration'   => $duration,
            'sched_count' => $scheds->count(),
            'busy_count' => $busy->count(),
        ]);

        $slots = [];
        $ds = $date->toDateString();

        foreach ($scheds as $s) {
            // --- Normaliza breaks (pueden venir como texto JSON) ---
            $rawBreaks = $s->breaks;
            if (!is_array($rawBreaks)) {
                $rawBreaks = json_decode($rawBreaks ?? '[]', true) ?: [];
            }
            $breaks = collect($rawBreaks)->map(fn($b) => [
                'start' => \Carbon\Carbon::parse("$ds {$b['start']}", $tz),
                'end'   => \Carbon\Carbon::parse("$ds {$b['end']}", $tz),
            ]);

            $cur = \Carbon\Carbon::parse("$ds {$s->start_time}", $tz);
            $end = \Carbon\Carbon::parse("$ds {$s->end_time}", $tz);

            while ($cur->copy()->addMinutes($duration)->lte($end)) {
                $slotStart = $cur->copy();
                $slotEnd   = $cur->copy()->addMinutes($duration);

                // hoy: oculta pasado
                if ($date->isSameDay($now) && $slotStart->lte($now)) {
                    $cur->addMinutes($duration);
                    continue;
                }

                $inBreak = $breaks->contains(
                    fn($br) =>
                    $slotStart->lt($br['end']) && $slotEnd->gt($br['start'])
                );
                $overlap = $busy->contains(
                    fn($iv) =>
                    $slotStart->lt($iv['end']) && $slotEnd->gt($iv['start'])
                );

                if (!$inBreak && !$overlap) {
                    $slots[] = $slotStart->format('H:i');
                }
                $cur->addMinutes($duration);
            }
        }

        $slots = array_values(array_unique($slots));
        \Log::debug('availability RESULT', ['count' => count($slots), 'slots' => $slots]);

        return response()->json($slots);
    }


    // POST /admin/citas  → guardar desde el formulario (redirige con flash)
    public function store(Request $r)
    {
        $data = $r->validate([
            'patient_id' => 'required|exists:patients,id',
            'dentist_id' => 'required|exists:dentists,id',
            'service_id' => 'required|exists:services,id',
            'date'       => 'required|date',
            'start_time' => 'required', // H:i u H:i:s
            'notes'      => 'nullable|string',
        ]);

        // Normaliza hora
        $startStr = strlen($data['start_time']) === 5 ? $data['start_time'] . ':00' : $data['start_time'];

        $svc   = Service::findOrFail($data['service_id']);
        $date  = Carbon::parse($data['date']);
        $start = Carbon::parse($data['date'] . ' ' . $startStr);
        $end   = $start->copy()->addMinutes($svc->duration_min);

        if ($start->isPast()) {
            return back()->withErrors(['start_time' => 'No se puede reservar en el pasado'])->withInput();
        }

        // VALIDACIÓN: Paciente activo
        $patient = Patient::find($data['patient_id']);
        if (!$patient || !$patient->is_active) {
             return back()->withErrors(['patient_id' => 'El paciente seleccionado ha sido desactivado.'])->withInput();
        }

        // VALIDACIÓN: Odontólogo activo y su usuario activo
        $dentist = Dentist::with('user')->find($data['dentist_id']);
        $isActive = $dentist && 
                    $dentist->status == 1 && 
                    (!$dentist->user || $dentist->user->status === 'active');

        if (!$isActive) {
             return back()->withErrors(['dentist_id' => 'El odontólogo seleccionado no está activo o su cuenta de usuario ha sido suspendida.'])->withInput();
        }

        // Conflicto con otras citas activas
        $conflict = Appointment::where('dentist_id', $data['dentist_id'])
            ->whereDate('date', $data['date'])->where('is_active', true)
            ->where('start_time', '<', $end->format('H:i:s'))
            ->where('end_time',   '>', $start->format('H:i:s'))
            ->exists();

        if ($conflict) {
            return back()->withErrors(['start_time' => 'Horario no disponible'])->withInput();
        }

        // === DETERMINAR SILLA ===
        // 1) buscar bloque de horario que cubra completamente el slot
        $dow = $date->dayOfWeek; // 0..6
        $block = Schedule::where('dentist_id', $data['dentist_id'])
            ->where('day_of_week', $dow)
            ->where('start_time', '<=', $start->format('H:i:s'))
            ->where('end_time',   '>=', $end->format('H:i:s'))
            ->orderBy('start_time', 'desc')
            ->first();

        if (!$block) {
            // Si no hay bloque que cubra ese tramo, mejor avisar (no debería pasar si usas availability)
            return back()->withErrors(['start_time' => 'El horario seleccionado no pertenece al turno del odontólogo.'])->withInput();
        }

        // 2) silla: la del bloque si existe, si no, la del odontólogo como fallback
        $chairId = $block->chair_id ?? Dentist::whereKey($data['dentist_id'])->value('chair_id');

        //validacion siempre con silla
        if (!$chairId) {
            return back()->withErrors(['start_time' => 'No hay silla asignada para ese turno.'])->withInput();
        }

        $appointment = Appointment::create([
            'patient_id' => $data['patient_id'],
            'dentist_id' => $data['dentist_id'],
            'service_id' => $data['service_id'],
            'chair_id'   => $chairId, // <- ahora correcto
            'date'       => $data['date'],
            'start_time' => $start->format('H:i:s'),
            'end_time'   => $end->format('H:i:s'),
            'status'     => 'reserved',
            'is_active'  => true,
            'notes'      => $data['notes'] ?? null,
        ]);

        // --- EMAIL: Confirmation ---
        try {
            if ($appointment->patient && $appointment->patient->email) {
                \Illuminate\Support\Facades\Mail::to($appointment->patient->email)
                    ->send(new \App\Mail\AppointmentConfirmation($appointment));

                \App\Models\EmailLog::create([
                    'to' => $appointment->patient->email,
                    'subject' => 'Confirmación de Cita - DentalCare',
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
             if ($appointment->patient && $appointment->patient->email) {
                \App\Models\EmailLog::create([
                    'to' => $appointment->patient->email,
                    'subject' => 'Confirmación de Cita - DentalCare',
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
             }
        }

        return redirect()->route('admin.appointments.index')->with('ok', 'Cita creada (correo enviado si corresponde)');
    }

    // Cambiar estado (desde listado) → redirige
    public function updateStatus(Request $r, Appointment $appointment)
    {
        $r->validate(['status' => 'required|in:reserved,confirmed,in_service,done,no_show,canceled']);
        $appointment->update(['status' => $r->status]);
        return back()->with('ok', 'Estado actualizado');
    }

    // Confirmación segura por email (Signed Route)
    public function confirmByEmail(Request $request, Appointment $appointment)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'El enlace ha expirado o no es válido.');
        }

        if ($appointment->status === 'canceled') {
            return "La cita fue cancelada anteriormente."; // O una vista más bonita
        }

        $appointment->update([
            'status' => 'confirmed',
            'is_active' => true
        ]);

        // Redireccionar a la vista de citas del paciente con mensaje de éxito
        return redirect()->route('app.appointments.index')
            ->with('ok', '¡Cita confirmada exitosamente! Te esperamos.');
    }

    // Cancelar (desde listado) → redirige
    public function cancel(Request $r, Appointment $appointment)
    {
        // si ya estaba cancelada, no hacemos nada
        if ($appointment->status === 'canceled') {
            return back()->with('ok', 'La cita ya estaba cancelada.');
        }

        $appointment->update([
            'status'          => 'canceled',
            'is_active'       => false,
            'canceled_at'     => now(),
            'canceled_reason' => $r->input('reason'),
        ]);

        return back()->with('ok', 'Cita cancelada y liberado el horario.');
    }

    public function slotChair(Request $r)
    {
        $r->validate([
            'dentist_id' => 'required|exists:dentists,id',
            'date'       => 'required|date',
            'time'       => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
        ]);

        $tz   = config('app.timezone', 'America/La_Paz');
        $date = Carbon::parse($r->date, $tz);
        $time = $r->time;
        if (strlen($time) === 5) $time .= ':00';

        $sched = Schedule::where('dentist_id', (int)$r->dentist_id)
            ->where('day_of_week', $date->dayOfWeek)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->first();

        if (!$sched || !$sched->chair_id) {
            return response()->json(['chair' => null]);
        }

        $chair = Chair::find($sched->chair_id);
        return response()->json([
            'chair' => $chair ? [
                'id'    => $chair->id,
                'name'  => $chair->name,
                'shift' => $chair->shift,
            ] : null
        ]);
    }
}
