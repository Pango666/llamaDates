@extends('patient.layout')
@section('title','Mis citas')

@section('header-actions')
  <a href="{{ route('app.appointments.create') }}" class="btn btn-primary inline-flex items-center gap-2">
    <i class="fas fa-plus"></i>
    Reservar
  </a>
@endsection

@php
  use Carbon\Carbon;

  $statusMap = [
    'reserved'   => ['label' => 'Reservada',   'cls' => 'bg-amber-50 text-amber-700 border-amber-200'],
    'confirmed'  => ['label' => 'Confirmada',  'cls' => 'bg-blue-50 text-blue-700 border-blue-200'],
    'in_service' => ['label' => 'En atención', 'cls' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
    'done'       => ['label' => 'Atendida',    'cls' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
    'completed'  => ['label' => 'Finalizada',  'cls' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],

    'no_show'         => ['label' => 'No asistió', 'cls' => 'bg-rose-50 text-rose-700 border-rose-200'],
    'non-attendance'  => ['label' => 'No asistió', 'cls' => 'bg-rose-50 text-rose-700 border-rose-200'],
    'non_attendance'  => ['label' => 'No asistió', 'cls' => 'bg-rose-50 text-rose-700 border-rose-200'],

    'canceled'   => ['label' => 'Cancelada',   'cls' => 'bg-slate-50 text-slate-700 border-slate-200 line-through'],
    'cancelled'  => ['label' => 'Cancelada',   'cls' => 'bg-slate-50 text-slate-700 border-slate-200 line-through'],
  ];

  $tabLabels = [
    'programadas' => 'Programadas',
    'asistidas'   => 'Asistidas',
    'no_asistio'  => 'No asistió',
    'canceladas'  => 'Canceladas',
    'todas'       => 'Todas',
  ];

  $fmtDate = fn($d) => Carbon::parse($d)->translatedFormat('D, d M Y');
  $fmtTime = fn($t) => \Illuminate\Support\Str::substr((string)$t, 0, 5);
@endphp

@section('content')
  <div class="max-w-5xl mx-auto">
    <div class="card border border-slate-200 mb-4">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="text-xs uppercase tracking-wide text-slate-500">Mostrando</div>
          <div class="font-semibold text-slate-800">{{ $tabLabels[$tab] ?? ucfirst($tab) }}</div>
        </div>

        <form method="get" class="flex items-center gap-2 flex-wrap">
          <span class="text-sm text-slate-500">Filtro:</span>
          <select name="tab" class="border border-slate-200 rounded-lg px-3 py-2 bg-white" onchange="this.form.submit()">
            <option value="programadas" {{ $tab==='programadas'?'selected':'' }}>Programadas</option>
            <option value="asistidas"   {{ $tab==='asistidas'?'selected':'' }}>Asistidas</option>
            <option value="no_asistio"  {{ $tab==='no_asistio'?'selected':'' }}>No asistió</option>
            <option value="canceladas"  {{ $tab==='canceladas'?'selected':'' }}>Canceladas</option>
            <option value="todas"       {{ $tab==='todas'?'selected':'' }}>Todas</option>
          </select>
        </form>
      </div>
    </div>

    <div class="grid gap-3">
      @forelse($appointments as $a)
        @php
          $rawStatus = strtolower((string)($a->status ?? 'reserved'));

          $st = $statusMap[$rawStatus]
              ?? ['label' => ucfirst(str_replace(['_','-'], ' ', $rawStatus)), 'cls' => 'bg-slate-50 text-slate-700 border-slate-200'];

          // Construct start datetime
          $startDateTime = \Carbon\Carbon::parse($a->date->format('Y-m-d') . ' ' . $a->start_time);
          $isFuture      = $startDateTime->isFuture();

          $canConfirm = ($rawStatus === 'reserved') && $isFuture;
          $canCancel  = in_array($rawStatus, ['reserved','confirmed'], true) && $isFuture;
        @endphp

        <div class="card border border-slate-200 hover:bg-slate-50 transition">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="inline-flex items-center text-xs px-2 py-1 rounded border {{ $st['cls'] }}">
                  {{ $st['label'] }}
                </span>
              </div>

              <div class="font-semibold text-slate-800">
                {{ $a->service->name ?? 'Consulta' }}
              </div>

              <div class="text-xs text-slate-500 mt-1 flex flex-wrap gap-x-3 gap-y-1">
                <span class="inline-flex items-center gap-1">
                  <i class="fas fa-calendar text-slate-400"></i>
                  {{ $fmtDate($a->date) }}
                </span>
                <span class="inline-flex items-center gap-1">
                  <i class="fas fa-clock text-slate-400"></i>
                  {{ $fmtTime($a->start_time) }}–{{ $fmtTime($a->end_time) }}
                </span>
                <span class="inline-flex items-center gap-1">
                  <i class="fas fa-user-doctor text-slate-400"></i>
                  {{ $a->dentist->name ?? '—' }}
                </span>
              </div>
            </div>

            <div class="shrink-0 flex flex-col items-end gap-2">
              <a href="{{ route('app.appointments.show', $a) }}"
                 class="btn btn-ghost text-sm inline-flex items-center gap-2">
                <i class="fas fa-eye"></i>
                Ver detalle
              </a>

              @if($canConfirm || $canCancel)
                <div class="flex items-center gap-2">
                  @if($canConfirm)
                    <form method="post"
                          action="{{ route('app.appointments.confirm',$a) }}"
                          onsubmit="return confirm('¿Confirmar esta cita?');">
                      @csrf
                      <button class="btn btn-primary inline-flex items-center gap-2">
                        <i class="fas fa-check"></i>
                        Confirmar
                      </button>
                    </form>
                  @endif

                  @if($canCancel)
                    <form method="post"
                          action="{{ route('app.appointments.cancel',$a) }}"
                          onsubmit="return confirm('¿Cancelar esta cita?');">
                      @csrf
                      <button class="btn btn-danger inline-flex items-center gap-2">
                        <i class="fas fa-xmark"></i>
                        Cancelar
                      </button>
                    </form>
                  @endif
                </div>
              @else
                <div class="text-xs text-slate-500">Sin acciones disponibles</div>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="card border border-dashed border-slate-300 text-center text-slate-500 py-10">
          No hay citas en este filtro.
          <div class="mt-3">
            <a href="{{ route('app.appointments.create') }}" class="btn btn-primary inline-flex items-center gap-2">
              <i class="fas fa-plus"></i>
              Reservar cita
            </a>
          </div>
        </div>
      @endforelse
    </div>

    <div class="mt-4">
      {{ $appointments->links() }}
    </div>
  </div>
@endsection
