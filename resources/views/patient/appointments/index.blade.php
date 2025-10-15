@extends('layouts.app')
@section('title','Mis citas')

@section('header-actions')
  <a href="{{ route('app.appointments.create') }}" class="btn btn-primary">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 5v14M5 12h14"/></svg>
    Reservar
  </a>
@endsection

@section('content')
  <div class="card mb-3">
    <form class="flex items-center flex-wrap gap-2">
      <span class="text-sm text-slate-500">Mostrar:</span>
      <select name="tab" class="border rounded px-2 py-1" onchange="this.form.submit()">
        <option value="programadas" {{ $tab==='programadas'?'selected':'' }}>Programadas</option>
        <option value="asistidas"   {{ $tab==='asistidas'?'selected':'' }}>Asistidas</option>
        <option value="no_asistio"  {{ $tab==='no_asistio'?'selected':'' }}>No asistió</option>
        <option value="canceladas"  {{ $tab==='canceladas'?'selected':'' }}>Canceladas</option>
        <option value="todas"       {{ $tab==='todas'?'selected':'' }}>Todas</option>
      </select>
    </form>
  </div>

  @php
    $badgeOf = fn($s) => [
      'reserved'   => 'bg-slate-100 text-slate-700',
      'confirmed'  => 'bg-blue-100 text-blue-700',
      'in_service' => 'bg-amber-100 text-amber-700',
      'done'       => 'bg-emerald-100 text-emerald-700',
      'no_show'    => 'bg-rose-100 text-rose-700',
      'canceled'   => 'bg-slate-200 text-slate-700 line-through',
      'cancelled'  => 'bg-slate-200 text-slate-700 line-through',
    ][$s] ?? 'bg-slate-100 text-slate-700';

    $labelOf = fn($s) => [
      'reserved'=>'Reservada','confirmed'=>'Confirmada','in_service'=>'En atención',
      'done'=>'Atendida','no_show'=>'No asistió','canceled'=>'Cancelada','cancelled'=>'Cancelada'
    ][$s] ?? $s;
  @endphp

  <div class="grid gap-3">
    @forelse($appointments as $a)
      @php
        $h       = strlen($a->start_time) === 5 ? $a->start_time.':00' : $a->start_time;
        $startAt = \Carbon\Carbon::parse($a->date)->setTimeFromTimeString($h);
        $canCancel = in_array($a->status, ['reserved','confirmed'], true) && now()->lt($startAt);
      @endphp

      <div class="card">
        <div class="flex items-center justify-between">
          <div>
            <div class="font-medium">{{ $a->service->name }}</div>
            <div class="text-xs text-slate-500">
              {{ \Illuminate\Support\Carbon::parse($a->date)->toDateString() }}
              · {{ \Illuminate\Support\Str::substr($a->start_time,0,5) }}–{{ \Illuminate\Support\Str::substr($a->end_time,0,5) }}
              con {{ $a->dentist->name }}
            </div>
            <div class="mt-1"><span class="badge {{ $badgeOf($a->status) }}">{{ $labelOf($a->status) }}</span></div>
          </div>

          @if($canCancel)
            <form method="post" action="{{ route('app.appointments.cancel',$a) }}" onsubmit="return confirm('¿Cancelar esta cita?');">
              @csrf
              <button class="btn btn-ghost">Cancelar</button>
            </form>
          @endif
        </div>
      </div>
    @empty
      <div class="text-sm text-slate-500">No hay citas en este filtro.</div>
    @endforelse
  </div>

  <div class="mt-3">{{ $appointments->links() }}</div>
@endsection
