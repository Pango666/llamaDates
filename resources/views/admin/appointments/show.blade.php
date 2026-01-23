@extends('layouts.app')
@section('title','Detalle de cita')

@section('header-actions')
@php
  $backUrl = url()->previous();
  if ($backUrl === url()->current()) {
    $backUrl = route('admin.appointments.index');
  }
@endphp

@can('appointments.view')
  <a href="{{ $backUrl }}" class="btn bg-slate-700 text-white hover:bg-slate-800 inline-flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver
  </a>
@endcan
@endsection

@section('content')
@php
  // ========= Traducciones =========
  $statusLabel = [
    'reserved'   => 'Reservada',
    'confirmed'  => 'Confirmada',
    'in_service' => 'En atención',
    'done'       => 'Finalizada',
    'no_show'    => 'No asistió',
    'canceled'   => 'Cancelada',
  ];

  $statusBadge = [
    'reserved'   => 'bg-slate-100 text-slate-700 border-slate-200',
    'confirmed'  => 'bg-blue-50 text-blue-700 border-blue-200',
    'in_service' => 'bg-amber-50 text-amber-800 border-amber-200',
    'done'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'no_show'    => 'bg-rose-50 text-rose-700 border-rose-200',
    'canceled'   => 'bg-slate-100 text-slate-500 border-slate-200 line-through',
  ];

  $statusIcon = [
    'reserved'   => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', // clock
    'confirmed'  => 'M5 13l4 4L19 7', // check
    'in_service' => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z', // play
    'done'       => 'M5 13l4 4L19 7', // check
    'no_show'    => 'M6 18L18 6M6 6l12 12', // x
    'canceled'   => 'M6 18L18 6M6 6l12 12', // x
  ];

  $badgeClass = $statusBadge[$appointment->status] ?? $statusBadge['reserved'];
  $badgeText  = $statusLabel[$appointment->status] ?? $appointment->status;

  // Solo se edita cuando está "En atención"
  $canEdit = $appointment->status === 'in_service';

  // Acciones rápidas: BLOQUEADAS hasta in_service
  $canQuickActions = $appointment->status === 'in_service';

  // Fallbacks por si no te los pasa el controller
  $notes = $notes ?? \App\Models\ClinicalNote::where('appointment_id',$appointment->id)->with('author')->latest()->get();
  $diagnoses = $diagnoses ?? \App\Models\Diagnosis::where('appointment_id',$appointment->id)->latest()->get();
  $attachments = $attachments ?? \App\Models\Attachment::where('appointment_id',$appointment->id)->latest()->get();

  if (empty($invoice)) {
    $invoice = \App\Models\Invoice::with(['items','payments'])
      ->where('appointment_id',$appointment->id)->latest()->first();
  }

  $totals = $totals ?? null;
  $isPaid = false;
  if ($invoice) {
    $subtotal = $invoice->items->sum('total');
    $discount = (float) $invoice->discount;
    $taxPct   = (float) $invoice->tax_percent;
    $base     = max($subtotal - $discount, 0);
    $grand    = $base + ($base * $taxPct / 100);
    $paid     = $invoice->payments->sum('amount');
    $due      = max($grand - $paid, 0);
    $totals   = compact('subtotal','base','grand','paid','due');
    $isPaid   = ($invoice->status === 'paid') || $due <= 0.0001;
  }

  $sups = $sups ?? \App\Models\AppointmentSupply::with(['product','location'])
           ->where('appointment_id',$appointment->id)->orderByDesc('id')->get();

  $sumCost = $sups->sum(fn($x) => (float)$x->unit_cost_at_issue * (float)$x->qty);

  $dateLabel = \Illuminate\Support\Carbon::parse($appointment->date)
      ->locale('es')
      ->translatedFormat('l, d \d\e F \d\e Y');

  $timeLabel = \Illuminate\Support\Str::substr($appointment->start_time,0,5) . '–' . \Illuminate\Support\Str::substr($appointment->end_time,0,5);
@endphp

<div class="max-w-7xl mx-auto space-y-4">

  {{-- ======= HERO / RESUMEN ======= --}}
  <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">

      <div class="flex-1">
        <div class="flex items-center gap-3 flex-wrap">
          <h1 class="text-2xl font-bold text-slate-900">
            Cita #{{ $appointment->id }}
          </h1>

          <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full border text-sm font-semibold {{ $badgeClass }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path d="{{ $statusIcon[$appointment->status] ?? $statusIcon['reserved'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            {{ $badgeText }}
          </span>

          @if($invoice)
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full border text-sm font-semibold {{ $isPaid ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if($isPaid)
                  <path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                @else
                  <path d="M12 8v4l3 3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <circle cx="12" cy="12" r="9" stroke-width="2"/>
                @endif
              </svg>
              Recibo: {{ $isPaid ? 'Pagado' : 'Pendiente' }}
              @if($totals)
                <span class="text-xs font-medium opacity-80">
                  · Saldo Bs {{ number_format($totals['due'], 2) }}
                </span>
              @endif
            </span>
          @endif
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
          {{-- Paciente --}}
          <div class="rounded-xl border border-slate-200 p-3 bg-slate-50">
            <div class="text-xs text-slate-500 mb-1">Paciente</div>
            <div class="font-semibold text-slate-900">
              {{ $appointment->patient->last_name }}, {{ $appointment->patient->first_name }}
            </div>
          </div>

          {{-- Odontólogo --}}
          <div class="rounded-xl border border-slate-200 p-3 bg-slate-50">
            <div class="text-xs text-slate-500 mb-1">Odontólogo</div>
            <div class="font-semibold text-slate-900">
              Dr. {{ $appointment->dentist->name }}
            </div>
          </div>

          {{-- Servicio --}}
          <div class="rounded-xl border border-slate-200 p-3 bg-slate-50">
            <div class="text-xs text-slate-500 mb-1">Servicio</div>
            <div class="font-semibold text-slate-900">
              {{ $appointment->service->name }}
            </div>
          </div>

          {{-- Fecha y hora --}}
          <div class="rounded-xl border border-slate-200 p-3 bg-slate-50">
            <div class="text-xs text-slate-500 mb-1">Fecha y hora</div>
            <div class="font-semibold text-slate-900">
              {{ $dateLabel }} · {{ $timeLabel }}
            </div>
          </div>
        </div>

        <div class="mt-4">
          <div class="text-xs text-slate-500 mb-1">Notas de la cita</div>
          <div class="text-sm text-slate-700">
            {{ $appointment->notes ?: 'Sin notas adicionales.' }}
          </div>
        </div>
      </div>

      {{-- ======= ACCIONES (ordenadas) ======= --}}
      <div class="w-full lg:w-[360px] space-y-3">
        <div class="rounded-2xl border border-slate-200 p-4 bg-white">
          <div class="text-sm font-semibold text-slate-900 mb-3">Acciones</div>

          @can('appointments.update')
            <form action="{{ route('admin.appointments.status',$appointment) }}" method="post" class="space-y-2">
              @csrf

              {{-- Acción primaria según estado --}}
              @if($appointment->status === 'confirmed')
                <button name="status" value="in_service"
                        class="w-full btn bg-blue-600 text-white hover:bg-blue-700 inline-flex items-center justify-center gap-2">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                  Iniciar atención
                </button>
              @elseif($appointment->status === 'in_service')
                <button name="status" value="done"
                        class="w-full btn bg-emerald-600 text-white hover:bg-emerald-700 inline-flex items-center justify-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  Finalizar atención
                </button>
              @else
                <div class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-lg p-3">
                  No hay acción principal disponible para el estado actual.
                </div>
              @endif

              {{-- Acciones financieras --}}
              @if($invoice)
                <a href="{{ route('admin.invoices.show',$invoice) }}"
                   class="w-full btn btn-ghost border border-slate-200 hover:bg-slate-50 inline-flex items-center justify-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M9 7h6M9 11h6M9 15h4" stroke-width="2" stroke-linecap="round"/>
                    <path d="M6 3h12a1 1 0 011 1v16l-4-3-4 3-4-3-4 3V4a1 1 0 011-1z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  {{ $isPaid ? 'Ver recibo pagado' : 'Ver / cobrar recibo' }}
                </a>
              @else
                <a href="{{ route('admin.invoices.createFromAppointment',$appointment->id) }}"
                   class="w-full btn btn-ghost border border-emerald-200 hover:bg-emerald-50 inline-flex items-center justify-center gap-2 text-emerald-700">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  Crear recibo
                </a>
              @endif

              {{-- Acción peligrosa: CANCELAR (con modal) --}}
              @can('appointments.cancel')
                @if(!in_array($appointment->status, ['done','canceled','no_show']))
                  <button type="button"
                          id="openCancelDialog"
                          class="w-full btn btn-ghost border border-red-200 text-red-700 hover:bg-red-50 inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path d="M18 6L6 18M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Cancelar cita…
                  </button>
                @endif
              @endcan
            </form>
          @endcan

          @if(!$canEdit && in_array($appointment->status, ['reserved','confirmed']))
            <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl">
              <div class="text-sm font-semibold text-amber-900">Edición bloqueada</div>
              <div class="text-xs text-amber-800 mt-1">
                Para editar notas/diagnósticos debes iniciar la atención.
              </div>
            </div>
          @endif
        </div>

        {{-- ======= Acciones rápidas (SOLO in_service) ======= --}}
        @if($canQuickActions)
          <div class="rounded-2xl border border-slate-200 p-4 bg-white">
            <div class="text-sm font-semibold text-slate-900 mb-3">Acciones rápidas</div>

            <div class="space-y-2">
              <a class="w-full btn btn-ghost justify-start border border-slate-200 hover:bg-slate-50 inline-flex items-center gap-2"
                 href="{{ route('admin.odontograms.open', ['patient'=>$appointment->patient_id]) }}">
                <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                Odontograma
              </a>

              <a class="w-full btn btn-ghost justify-start border border-slate-200 hover:bg-slate-50 inline-flex items-center gap-2"
                 href="{{ route('admin.patients.consents.create', ['patient'=>$appointment->patient_id, 'appointment_id'=>$appointment->id]) }}">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                Consentimiento PDF
              </a>

              <a class="w-full btn btn-ghost justify-start border border-slate-200 hover:bg-slate-50 inline-flex items-center gap-2"
                 href="{{ route('admin.patients.consents.index', $appointment->patient_id) }}">
                <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                Ver consentimientos
              </a>
            </div>

            @if($totals)
              <div class="mt-4 pt-4 border-t border-slate-200">
                <div class="text-xs text-slate-500 mb-2">Resumen del recibo</div>
                <div class="text-sm text-slate-700 space-y-1">
                  <div class="flex justify-between">
                    <span>Total</span>
                    <span class="font-semibold">Bs {{ number_format($totals['grand'],2) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Pagado</span>
                    <span class="font-semibold">Bs {{ number_format($totals['paid'],2) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Saldo</span>
                    <span class="font-semibold {{ ($totals['due'] ?? 0) > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                      Bs {{ number_format($totals['due'],2) }}
                    </span>
                  </div>
                </div>
              </div>
            @endif
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- ======= CUERPO: CLÍNICA ======= --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 indicated space-y-4">

      {{-- NOTAS CLÍNICAS --}}
      <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-bold text-slate-900">Notas clínicas</h2>
            <p class="text-xs text-slate-500">
              {{ $canEdit ? 'Edición habilitada (En atención).' : 'Solo lectura.' }}
            </p>
          </div>

          @if($canEdit && \Illuminate\Support\Facades\Route::has('admin.notes.create'))
            <a href="{{ route('admin.notes.create', ['patient_id'=>$appointment->patient_id, 'appointment_id'=>$appointment->id]) }}"
               class="btn bg-slate-900 text-white hover:bg-slate-800 inline-flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Nueva nota
            </a>
          @endif
        </div>

        @if($canEdit)
          <form method="post" action="{{ route('admin.appointments.notes.store',$appointment) }}"
                class="mb-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
            @csrf
            <input type="hidden" name="type" value="SOAP">

            <div class="grid gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">S - Subjetivo</label>
                <textarea name="subjective" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"
                          placeholder="Lo que el paciente reporta..."></textarea>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">O - Objetivo</label>
                <textarea name="objective" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"
                          placeholder="Hallazgos clínicos..."></textarea>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">A - Evaluación</label>
                <textarea name="assessment" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"
                          placeholder="Impresión diagnóstica..."></textarea>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">P - Plan</label>
                <textarea name="plan" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"
                          placeholder="Plan de tratamiento..."></textarea>
              </div>
            </div>

            <div class="flex flex-wrap gap-2 mt-3">
              <button class="btn bg-blue-600 text-white hover:bg-blue-700 inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Guardar nota
              </button>
              <button type="button" onclick="this.form.reset()"
                      class="btn btn-ghost border border-slate-200 hover:bg-slate-100 inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path d="M4 4v6h6M20 20v-6h-6M20 4l-6 6M4 20l6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Limpiar
              </button>
            </div>
          </form>
        @endif

        <div class="space-y-3">
          @forelse($notes as $n)
            <div class="border border-slate-200 rounded-xl p-4 bg-white">
              <div class="flex items-start justify-between gap-3 mb-2">
                <div class="text-xs text-slate-500">
                  {{ $n->created_at->format('d/m H:i') }}
                  @if($n->author) · {{ $n->author->name }} @endif
                </div>

                @if($canEdit)
                  <form method="post" action="{{ route('admin.notes.destroy',$n) }}"
                        onsubmit="return confirm('¿Eliminar nota?');" class="inline">
                    @csrf @method('DELETE')
                    <button class="text-red-600 hover:text-red-800 text-xs inline-flex items-center gap-1" title="Eliminar">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M6 7h12M10 11v6M14 11v6M9 7l1-2h4l1 2m-9 0h10v12a2 2 0 01-2 2H7a2 2 0 01-2-2V7z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                    </button>
                  </form>
                @endif
              </div>

              <div class="text-sm text-slate-700 space-y-1">
                @if($n->subjective)<div><span class="font-semibold text-slate-600">S:</span> {{ $n->subjective }}</div>@endif
                @if($n->objective) <div><span class="font-semibold text-slate-600">O:</span> {{ $n->objective }}</div>@endif
                @if($n->assessment)<div><span class="font-semibold text-slate-600">A:</span> {{ $n->assessment }}</div>@endif
                @if($n->plan)      <div><span class="font-semibold text-slate-600">P:</span> {{ $n->plan }}</div>@endif
              </div>
            </div>
          @empty
            <div class="text-center py-6 text-slate-500">
              No hay notas clínicas registradas.
            </div>
          @endforelse
        </div>
      </section>

      {{-- DIAGNÓSTICOS --}}
      <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-bold text-slate-900">Diagnósticos</h2>
            <p class="text-xs text-slate-500">{{ $canEdit ? 'Edición habilitada.' : 'Solo lectura.' }}</p>
          </div>
        </div>

        @if($canEdit)
          <form method="post" action="{{ route('admin.appointments.diagnoses.store',$appointment) }}"
                class="mb-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <input name="label" class="border border-slate-300 rounded-lg px-3 py-2 text-sm md:col-span-2"
                     placeholder="Diagnóstico (ej: Caries dental)" required>
              <input name="code" class="border border-slate-300 rounded-lg px-3 py-2 text-sm"
                     placeholder="Código CIE-10">
              <input name="tooth_code" class="border border-slate-300 rounded-lg px-3 py-2 text-sm"
                     placeholder="Pieza (ej: 26)">
              <select name="surface" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Superficie</option>
                <option>O</option><option>M</option><option>D</option>
                <option>B</option><option>L</option><option>I</option>
              </select>
              <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
                <option value="active">Activo</option>
                <option value="resolved">Resuelto</option>
              </select>

              <div class="md:col-span-2 flex flex-col md:flex-row gap-2 mt-1">
                <input name="notes" class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm"
                       placeholder="Notas adicionales (opcional)">
                <button class="btn bg-emerald-600 text-white hover:bg-emerald-700 inline-flex items-center justify-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  Agregar
                </button>
              </div>
            </div>
          </form>
        @endif

        <div class="space-y-3">
          @forelse($diagnoses as $d)
            <div class="border border-slate-200 rounded-xl p-4 bg-white">
              <div class="flex items-start justify-between gap-3">
                <div class="font-semibold text-slate-900">{{ $d->label }}</div>

                @if($canEdit)
                  <form method="post" action="{{ route('admin.diagnoses.destroy',$d) }}"
                        onsubmit="return confirm('¿Eliminar diagnóstico?');">
                    @csrf @method('DELETE')
                    <button class="text-red-600 hover:text-red-800 text-xs inline-flex items-center gap-1" title="Eliminar">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M6 7h12M10 11v6M14 11v6M9 7l1-2h4l1 2m-9 0h10v12a2 2 0 01-2 2H7a2 2 0 01-2-2V7z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                    </button>
                  </form>
                @endif
              </div>

              <div class="text-xs text-slate-600 mt-2 space-y-1">
                @if($d->code)<div><span class="font-semibold">CIE-10:</span> {{ $d->code }}</div>@endif
                @if($d->tooth_code)
                  <div><span class="font-semibold">Pieza:</span> {{ $d->tooth_code }} @if($d->surface)· {{ $d->surface }}@endif</div>
                @endif

                <div class="flex items-center justify-between pt-2">
                  <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full border text-xs font-semibold
                    {{ $d->status==='active' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                    {{ $d->status==='active' ? 'Activo' : 'Resuelto' }}
                  </span>
                  <span class="text-slate-500">{{ $d->created_at->format('d/m H:i') }}</span>
                </div>

                @if($d->notes)
                  <div class="text-sm text-slate-700 mt-2">{{ $d->notes }}</div>
                @endif
              </div>
            </div>
          @empty
            <div class="text-center py-6 text-slate-500">
              No hay diagnósticos registrados.
            </div>
          @endforelse
        </div>
      </section>

      {{-- ARCHIVOS ADJUNTOS --}}
      <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-bold text-slate-900">Archivos adjuntos</h2>
            <p class="text-xs text-slate-500">Imágenes y PDFs asociados a la cita.</p>
          </div>

          @if($canEdit)
            <button type="button" onclick="document.getElementById('attachment-form').classList.toggle('hidden')"
                    class="btn bg-slate-900 text-white hover:bg-slate-800 inline-flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Subir archivos
            </button>
          @endif
        </div>

        @if($canEdit)
          <form id="attachment-form" method="post" action="{{ route('admin.appointments.attachments.store',$appointment) }}"
                enctype="multipart/form-data" class="hidden mb-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
            @csrf
            <div class="flex flex-col md:flex-row gap-2">
              <input type="file" name="files[]" multiple class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm"
                     accept="image/*,application/pdf">
              <input type="text" name="notes" class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm"
                     placeholder="Descripción (opcional)">
              <button class="btn bg-blue-600 text-white hover:bg-blue-700 inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path d="M4 12h16M12 4v16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Subir
              </button>
            </div>
          </form>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          @forelse($attachments as $a)
            <div class="border border-slate-200 rounded-xl p-4 hover:bg-slate-50">
              <div class="flex items-start justify-between gap-2">
                <div class="font-semibold text-sm text-slate-900 truncate">{{ $a->original_name }}</div>

                @if($canEdit)
                  <form method="post" action="{{ route('admin.attachments.destroy',$a) }}"
                        onsubmit="return confirm('¿Eliminar archivo?');">
                    @csrf @method('DELETE')
                    <button class="text-red-600 hover:text-red-800 text-xs" title="Eliminar">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M6 7h12M10 11v6M14 11v6M9 7l1-2h4l1 2m-9 0h10v12a2 2 0 01-2 2H7a2 2 0 01-2-2V7z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                    </button>
                  </form>
                @endif
              </div>

              <div class="text-xs text-slate-500 mt-1">
                {{ $a->created_at->format('d/m H:i') }} · {{ strtoupper($a->type ?: 'archivo') }}
              </div>

              <div class="flex items-center gap-2 mt-3">
                <a class="btn btn-ghost border border-slate-200 hover:bg-slate-100 text-xs inline-flex items-center gap-1"
                   href="{{ asset('storage/'.$a->path) }}" target="_blank">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S3.732 16.057 2.458 12z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  Ver
                </a>
                @if($a->notes)
                  <span class="text-xs text-slate-600 truncate">{{ $a->notes }}</span>
                @endif
              </div>
            </div>
          @empty
            <div class="md:col-span-2 text-center py-6 text-slate-500">
              No hay archivos adjuntos.
            </div>
          @endforelse
        </div>
      </section>

    </div>

    {{-- Columna derecha (información del paciente) --}}
    <aside class="space-y-4">
      {{-- ======= ALERGIAS Y ALERTAS MÉDICAS ======= --}}
      @php
        $medHistory = $appointment->patient->medicalHistory ?? null;
        $hasAllergies = $medHistory && !empty(trim($medHistory->allergies ?? ''));
        $hasMedications = $medHistory && !empty(trim($medHistory->medications ?? ''));
        $hasDiseases = $medHistory && !empty(trim($medHistory->systemic_diseases ?? ''));
        $isSmoker = $medHistory && $medHistory->smoker;
        $isPregnant = $medHistory && $medHistory->pregnant;
        $hasAnyMedicalInfo = $hasAllergies || $hasMedications || $hasDiseases || $isSmoker || $isPregnant;
      @endphp

      {{-- Alerta de alergias (destacada si tiene) --}}
      @if($hasAllergies)
        <div class="bg-red-50 border border-red-200 rounded-2xl shadow-sm p-5">
          <div class="flex items-start gap-3">
            <div class="p-2 bg-red-100 rounded-lg">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
              </svg>
            </div>
            <div class="flex-1">
              <h3 class="font-bold text-red-900 flex items-center gap-2">
                ⚠️ Alergias Conocidas
              </h3>
              <p class="text-sm text-red-800 mt-2 whitespace-pre-line">{{ $medHistory->allergies }}</p>
            </div>
          </div>
        </div>
      @endif

      {{-- Alertas de fumador/embarazada --}}
      @if($isSmoker || $isPregnant)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl shadow-sm p-4">
          <div class="flex flex-wrap gap-2">
            @if($isSmoker)
              <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-100 text-amber-800 text-sm font-medium border border-amber-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                </svg>
                Paciente Fumador
              </span>
            @endif
            @if($isPregnant)
              <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-pink-100 text-pink-800 text-sm font-medium border border-pink-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                Paciente Embarazada
              </span>
            @endif
          </div>
        </div>
      @endif

      {{-- Información médica general --}}
      <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-bold text-slate-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Información Médica
          </h3>
          @can('patients.edit')
            <a href="{{ route('admin.patients.edit', $appointment->patient) }}" 
               class="btn btn-ghost text-xs text-blue-600 hover:bg-blue-50 inline-flex items-center gap-1"
               title="Editar información médica">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Editar
            </a>
          @endcan
        </div>

        @if($hasAnyMedicalInfo)
          <div class="space-y-4">
            {{-- Alergias (si no se mostró arriba como alerta) --}}
            @if(!$hasAllergies)
              <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                <div class="text-xs font-medium text-green-700 flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  Sin alergias conocidas
                </div>
              </div>
            @endif

            {{-- Medicamentos actuales --}}
            @if($hasMedications)
              <div>
                <div class="text-xs font-semibold text-slate-500 mb-1 flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                  </svg>
                  Medicamentos Actuales
                </div>
                <p class="text-sm text-slate-700 whitespace-pre-line bg-slate-50 p-2 rounded-lg">{{ $medHistory->medications }}</p>
              </div>
            @endif

            {{-- Enfermedades sistémicas --}}
            @if($hasDiseases)
              <div>
                <div class="text-xs font-semibold text-slate-500 mb-1 flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                  </svg>
                  Enfermedades Sistémicas
                </div>
                <p class="text-sm text-slate-700 whitespace-pre-line bg-slate-50 p-2 rounded-lg">{{ $medHistory->systemic_diseases }}</p>
              </div>
            @endif
          </div>
        @else
          {{-- Sin información médica --}}
          <div class="text-center py-6">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm text-slate-500">Sin información médica registrada</p>
            @can('patients.edit')
              <a href="{{ route('admin.patients.edit', $appointment->patient) }}" 
                 class="btn btn-ghost text-sm text-blue-600 hover:bg-blue-50 mt-2 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Agregar información
              </a>
            @endcan
          </div>
        @endif
      </div>

      {{-- Enlace rápido al perfil del paciente --}}
      <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
        <a href="{{ route('admin.patients.show', $appointment->patient) }}" 
           class="flex items-center gap-3 text-slate-700 hover:text-blue-600 transition-colors">
          <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <div class="flex-1">
            <div class="font-semibold">{{ $appointment->patient->last_name }}, {{ $appointment->patient->first_name }}</div>
            <div class="text-xs text-slate-500">Ver perfil completo →</div>
          </div>
        </a>
      </div>
    </aside>
  </div>

  {{-- ======= SUMINISTROS (COMENTADO - Funcionalidad deshabilitada temporalmente) ======= --}}
  {{--
  <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="text-lg font-bold text-slate-900">Suministros usados</h2>
        <p class="text-xs text-slate-500">Registro de productos consumidos durante la atención.</p>
      </div>

      <div class="text-sm font-semibold text-slate-700">
        Total consumido: <span class="text-slate-900">Bs {{ number_format($sumCost, 2) }}</span>
      </div>
    </div>

    <div class="overflow-x-auto border border-slate-200 rounded-xl">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 border-b">
          <tr>
            <th class="px-4 py-3 text-left font-semibold text-slate-700">Producto</th>
            <th class="px-4 py-3 text-right font-semibold text-slate-700">Cantidad</th>
            <th class="px-4 py-3 text-right font-semibold text-slate-700">Costo unit.</th>
            <th class="px-4 py-3 text-right font-semibold text-slate-700">Total</th>
          </tr>
        </thead>
        <tbody>
          @forelse($sups as $s)
            @php
              $u = (float)$s->unit_cost_at_issue;
              $q = (float)$s->qty;
            @endphp
            <tr class="border-b hover:bg-slate-50">
              <td class="px-4 py-3">
                <div class="font-semibold text-slate-900">{{ $s->product->name ?? '#'.$s->product_id }}</div>
                <div class="text-xs text-slate-500">
                  {{ $s->location->name ?? '—' }} @if($s->lot) · Lote: {{ $s->lot }}@endif
                </div>
              </td>
              <td class="px-4 py-3 text-right text-slate-700">
                {{ rtrim(rtrim(number_format($q,3,'.',''), '0'),'.') }}
              </td>
              <td class="px-4 py-3 text-right text-slate-700">{{ number_format($u, 4) }}</td>
              <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format($u*$q, 2) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-4 py-10 text-center text-slate-500">
                No hay suministros registrados.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
  --}}

</div>

{{-- ======= MODAL CANCELAR (SEGURIDAD ANTI-ERROR) ======= --}}
@if(!in_array($appointment->status, ['done','canceled','no_show']))
<dialog id="cancelDialog" class="rounded-2xl p-0 w-full max-w-lg">
  <form method="post" action="{{ route('admin.appointments.status',$appointment) }}" class="bg-white rounded-2xl">
    @csrf
    <input type="hidden" name="status" value="canceled">

    <div class="p-5 border-b border-slate-200">
      <div class="flex items-start justify-between gap-3">
        <div>
          <h3 class="text-lg font-bold text-slate-900">Cancelar cita</h3>
          <p class="text-sm text-slate-600 mt-1">
            Esta acción es delicada. Para evitar errores, confirma con motivo y escribe <b>CANCELAR</b>.
          </p>
        </div>
        <button type="button" id="closeCancelDialog" class="btn btn-ghost border border-slate-200 hover:bg-slate-100">Cerrar</button>
      </div>
    </div>

    <div class="p-5 space-y-3">
      <div class="p-3 rounded-xl border border-amber-200 bg-amber-50 text-amber-900 text-sm">
        <b>Resumen:</b> {{ $appointment->patient->last_name }}, {{ $appointment->patient->first_name }}
        · {{ $dateLabel }} · {{ $timeLabel }}
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Motivo</label>
        <select name="cancel_reason" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" required>
          <option value="">Selecciona un motivo…</option>
          <option value="patient_request">Solicitud del paciente</option>
          <option value="clinic_issue">Problema de consultorio/agenda</option>
          <option value="dentist_unavailable">Odontólogo no disponible</option>
          <option value="other">Otro</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Escribe “CANCELAR” para confirmar</label>
        <input id="cancelConfirmInput" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"
               placeholder="CANCELAR" autocomplete="off">
      </div>

      <div class="text-xs text-slate-500">
        Tip: Si la cita no se presentó, usa el estado <b>No asistió</b> (cuando lo implementes) en vez de cancelar.
      </div>
    </div>

    <div class="p-5 border-t border-slate-200 flex flex-col sm:flex-row gap-2 justify-end">
      <button type="button" id="cancelDialogAbort" class="btn btn-ghost border border-slate-200 hover:bg-slate-100">
        No cancelar
      </button>

      <button id="cancelDialogSubmit" class="btn bg-red-600 text-white hover:bg-red-700" disabled>
        Sí, cancelar cita
      </button>
    </div>
  </form>
</dialog>

<script>
(function () {
  const openBtn  = document.getElementById('openCancelDialog');
  const dialog   = document.getElementById('cancelDialog');
  const closeBtn = document.getElementById('closeCancelDialog');
  const abortBtn = document.getElementById('cancelDialogAbort');
  const input    = document.getElementById('cancelConfirmInput');
  const submit   = document.getElementById('cancelDialogSubmit');

  if (!openBtn || !dialog) return;

  function open() {
    if (typeof dialog.showModal === 'function') dialog.showModal();
    else dialog.setAttribute('open', 'open');
    if (input) input.value = '';
    if (submit) submit.disabled = true;
    setTimeout(() => input && input.focus(), 50);
  }
  function close() {
    if (typeof dialog.close === 'function') dialog.close();
    else dialog.removeAttribute('open');
  }

  openBtn.addEventListener('click', open);
  closeBtn && closeBtn.addEventListener('click', close);
  abortBtn && abortBtn.addEventListener('click', close);

  input && input.addEventListener('input', function () {
    const ok = String(input.value || '').trim().toUpperCase() === 'CANCELAR';
    if (submit) submit.disabled = !ok;
  });

  dialog.addEventListener('click', function(e) {
    const rect = dialog.getBoundingClientRect();
    const inDialog =
      e.clientX >= rect.left && e.clientX <= rect.right &&
      e.clientY >= rect.top && e.clientY <= rect.bottom;
    if (!inDialog) close();
  });
})();
</script>
@endif
@endsection
