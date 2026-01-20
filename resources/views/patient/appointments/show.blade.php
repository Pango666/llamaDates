@extends('patient.layout')
@section('title','Cita')

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

  $rawStatus = strtolower((string)($appointment->status ?? 'reserved'));
  $st = $statusMap[$rawStatus]
      ?? ['label' => ucfirst(str_replace(['_','-'], ' ', $rawStatus)), 'cls' => 'bg-slate-50 text-slate-700 border-slate-200'];

  $canConfirm = ($rawStatus === 'reserved');
  $canCancel  = in_array($rawStatus, ['reserved','confirmed'], true);

  $fmtDate = fn($d) => Carbon::parse($d)->translatedFormat('l, d F Y');
  $fmtTime = fn($t) => \Illuminate\Support\Str::substr((string)$t, 0, 5);
@endphp

@section('content')
  <div class="max-w-5xl mx-auto grid gap-4">

    <div class="flex items-center justify-between gap-3 flex-wrap">
      <a href="{{ route('app.appointments.index') }}" class="btn btn-ghost inline-flex items-center gap-2">
        <i class="fas fa-arrow-left"></i>
        Volver
      </a>

      @if($canConfirm || $canCancel)
        <div class="flex items-center gap-2">
          @if($canConfirm)
            <form method="post"
                  action="{{ route('app.appointments.confirm',$appointment) }}"
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
                  action="{{ route('app.appointments.cancel',$appointment) }}"
                  onsubmit="return confirm('¿Cancelar esta cita?');">
              @csrf
              <button class="btn btn-danger inline-flex items-center gap-2">
                <i class="fas fa-xmark"></i>
                Cancelar
              </button>
            </form>
          @endif
        </div>
      @endif
    </div>

    <div class="card mb-4 border border-slate-200">
      <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
          <div class="flex items-center gap-2 flex-wrap mb-2">
            <span class="inline-flex items-center text-xs px-2 py-1 rounded border {{ $st['cls'] }}">
              {{ $st['label'] }}
            </span>
          </div>

          <h3 class="font-semibold text-slate-800 text-lg">{{ $appointment->service->name }}</h3>

          <div class="mt-2 text-sm text-slate-600 flex flex-wrap gap-x-4 gap-y-1">
            <span class="inline-flex items-center gap-2">
              <i class="fas fa-calendar text-slate-400"></i>
              {{ $fmtDate($appointment->date) }}
            </span>
            <span class="inline-flex items-center gap-2">
              <i class="fas fa-clock text-slate-400"></i>
              {{ $fmtTime($appointment->start_time) }}–{{ $fmtTime($appointment->end_time) }}
            </span>
            <span class="inline-flex items-center gap-2">
              <i class="fas fa-user-doctor text-slate-400"></i>
              {{ $appointment->dentist->name }}
            </span>
            <span class="inline-flex items-center gap-2">
              <i class="fas fa-circle-info text-slate-400"></i>
              Estado: <span class="font-medium text-slate-800">{{ $st['label'] }}</span>
            </span>
          </div>
        </div>
      </div>

      <div class="mt-4 pt-4 border-t border-slate-200">
        <div class="text-xs uppercase tracking-wide text-slate-500 mb-2">Notas</div>
        <div class="text-sm text-slate-700">{{ $appointment->notes ?: '—' }}</div>
      </div>

      @if(!$canConfirm && !$canCancel)
        <div class="mt-4 pt-4 border-t border-slate-200 text-xs text-slate-500">
          Esta cita no tiene acciones disponibles en el estado actual.
        </div>
      @endif
    </div>

    <div class="card border border-slate-200">
      <h4 class="font-semibold text-slate-800 mb-2">Adjuntos</h4>
      @forelse($appointment->attachments as $att)
        <div class="flex justify-between border-b last:border-0 py-2 text-sm">
          <div class="min-w-0">
            <div class="truncate text-slate-700">{{ $att->original_name }}</div>
          </div>
          <a class="btn btn-ghost inline-flex items-center gap-2"
             target="_blank"
             href="{{ asset('storage/'.$att->path) }}">
            <i class="fas fa-eye"></i>
            Ver
          </a>
        </div>
      @empty
        <div class="text-sm text-slate-500">Sin adjuntos.</div>
      @endforelse
    </div>

  </div>
@endsection
