@extends('layouts.app')
@section('title','Citas')

{{-- Botón en el header sticky --}}
@section('header-actions')
  <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">+ Nueva cita</a>
@endsection

@section('content')
  {{-- Filtros --}}
  <form method="get" class="card mb-4">
    <div class="grid gap-3 md:grid-cols-5">
      <div>
        <label class="block text-xs text-slate-500 mb-1">Fecha</label>
        <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="w-full border rounded px-2 py-2">
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Odontólogo</label>
        <select name="dentist_id" class="w-full border rounded px-2 py-2">
          <option value="">— Todos —</option>
          @foreach($dentists as $d)
            <option value="{{ $d->id }}" @selected(($filters['dentist_id'] ?? null)==$d->id)>{{ $d->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Estado</label>
        <select name="status" class="w-full border rounded px-2 py-2">
          <option value="">— Todos —</option>
          @foreach(['reserved'=>'Reservado','confirmed'=>'Confirmado','in_service'=>'En atención','done'=>'Atendido','no_show'=>'No asistió','canceled'=>'Cancelado'] as $val=>$label)
            <option value="{{ $val }}" @selected(($filters['status'] ?? null)===$val)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="flex items-end gap-2">
        <button class="btn btn-ghost">Filtrar</button>
        @if(request()->hasAny(['date','dentist_id','status']))
          <a href="{{ route('admin.appointments.index') }}" class="btn btn-ghost">Limpiar</a>
        @endif
      </div>
    </div>
  </form>

  {{-- Tabla --}}
  <div class="card p-0">
    {{-- El scroll va en este div, no en la tarjeta --}}
    <div class="overflow-auto max-h-[70vh]">
      <table class="min-w-full text-sm">
        <thead class="sticky top-0 z-10 bg-white border-b">
          <tr class="text-left">
            <th class="px-3 py-2 bg-white">Fecha</th>
            <th class="px-3 py-2 bg-white">Hora</th>
            <th class="px-3 py-2 bg-white">Paciente</th>
            <th class="px-3 py-2 bg-white">Servicio</th>
            <th class="px-3 py-2 bg-white">Odontólogo</th>
            <th class="px-3 py-2 bg-white">Estado</th>
            <th class="px-3 py-2 bg-white text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($appointments as $a)
            @php
              $dateStr = \Illuminate\Support\Carbon::parse($a->date)->toDateString();
              $endTs   = \Illuminate\Support\Carbon::parse($dateStr)->setTimeFromTimeString($a->end_time);
              $locked  = $endTs->isPast();

              $badge = [
                'reserved'   => 'bg-slate-100 text-slate-700',
                'confirmed'  => 'bg-blue-100 text-blue-700',
                'in_service' => 'bg-amber-100 text-amber-700',
                'done'       => 'bg-emerald-100 text-emerald-700',
                'no_show'    => 'bg-rose-100 text-rose-700',
                'canceled'   => 'bg-slate-200 text-slate-700 line-through',
              ][$a->status] ?? 'bg-slate-100 text-slate-700';
            @endphp
            <tr class="border-b hover:bg-slate-50">
              <td class="px-3 py-2 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($a->date)->format('Y-m-d') }}</td>
              <td class="px-3 py-2 whitespace-nowrap">{{ \Illuminate\Support\Str::substr($a->start_time,0,5) }}–{{ \Illuminate\Support\Str::substr($a->end_time,0,5) }}</td>
              <td class="px-3 py-2">{{ $a->patient->last_name }}, {{ $a->patient->first_name }}</td>
              <td class="px-3 py-2">{{ $a->service->name }}</td>
              <td class="px-3 py-2">{{ $a->dentist->name }}</td>
              <td class="px-3 py-2">
                <span class="badge {{ $badge }}">{{ [
                  'reserved'=>'Reservado','confirmed'=>'Confirmado','in_service'=>'En atención',
                  'done'=>'Atendido','no_show'=>'No asistió','canceled'=>'Cancelado'
                ][$a->status] ?? $a->status }}</span>
              </td>
              <td class="px-3 py-2">
                <div class="flex items-center justify-end gap-2">
                  <a href="{{ route('admin.appointments.show',$a) }}" class="btn btn-ghost">Ver</a>

                  @if(!$locked)
                    <form action="{{ route('admin.appointments.status',$a) }}" method="post">
                      @csrf
                      <select name="status" class="border rounded px-2 py-1 text-xs" onchange="this.form.submit()">
                        @foreach(['reserved'=>'Reservado','confirmed'=>'Confirmado','in_service'=>'En atención','done'=>'Atendido','no_show'=>'No asistió','canceled'=>'Cancelado'] as $val=>$label)
                          <option value="{{ $val }}" @selected($a->status===$val)>{{ $label }}</option>
                        @endforeach
                      </select>
                    </form>
                    @if($a->is_active && $a->status!=='canceled')
                      <form action="{{ route('admin.appointments.cancel',$a) }}" method="post" onsubmit="return confirm('¿Cancelar esta cita?');">
                        @csrf
                        <button class="btn btn-danger btn-sm">Cancelar</button>
                      </form>
                    @endif
                  @else
                    <span class="text-xs text-slate-400">Solo lectura</span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="px-3 py-6 text-center text-slate-500">No hay citas con esos filtros.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">{{ $appointments->links() }}</div>
@endsection
