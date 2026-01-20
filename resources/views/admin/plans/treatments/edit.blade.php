@extends('layouts.app')
@section('title','Editar tratamiento')

@section('header-actions')
  <a href="{{ route('admin.plans.edit',$plan) }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver al plan
  </a>
@endsection

@section('content')
<div class="card max-w-3xl">
  <form method="post" action="{{ route('admin.plans.treatments.update', [$plan, $treatment]) }}" class="grid md:grid-cols-2 gap-4">
    @csrf
    @method('PUT')

    {{-- Servicio --}}
    <div class="md:col-span-2">
      <label class="block text-xs text-slate-500 mb-1">Servicio</label>
      <select name="service_id" class="w-full border rounded px-3 py-2" required>
        @foreach($services as $s)
          <option value="{{ $s->id }}"
            @selected(old('service_id',$treatment->service_id)==$s->id)>
            {{ $s->name }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- Odontólogo (planificado) --}}
    <div>
      <label class="block text-xs text-slate-500 mb-1">Odontólogo (planificado)</label>
      <select name="dentist_id" class="w-full border rounded px-3 py-2">
        <option value="">— Sin asignar —</option>
        @foreach($dentists as $d)
          <option value="{{ $d->id }}"
            @selected(old('dentist_id', $treatment->dentist_id) == $d->id)>
            {{ $d->name }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- Fecha planificada --}}
    <div>
      <label class="block text-xs text-slate-500 mb-1">Fecha (planificada)</label>
      <input
        type="date"
        name="planned_date"
        class="w-full border rounded px-3 py-2"
        value="{{ old('planned_date', optional($treatment->planned_date)->format('Y-m-d')) }}"
        min="{{ now()->toDateString() }}"
      >
    </div>

    {{-- Hora inicio --}}
    <div>
      <label class="block text-xs text-slate-500 mb-1">Hora inicio (planificada)</label>
      <input
        type="time"
        name="planned_start_time"
        class="w-full border rounded px-3 py-2"
        value="{{ old('planned_start_time', $treatment->planned_start_time ? \Illuminate\Support\Str::of($treatment->planned_start_time)->substr(0,5) : '') }}"
      >
    </div>

    {{-- Hora fin --}}
    <div>
      <label class="block text-xs text-slate-500 mb-1">Hora fin (planificada)</label>
      <input
        type="time"
        name="planned_end_time"
        class="w-full border rounded px-3 py-2"
        value="{{ old('planned_end_time', $treatment->planned_end_time ? \Illuminate\Support\Str::of($treatment->planned_end_time)->substr(0,5) : '') }}"
      >
    </div>

    {{-- Pieza --}}
    <div>
      <label class="block text-xs text-slate-500 mb-1">Pieza</label>
      <input
        name="tooth_code"
        value="{{ old('tooth_code',$treatment->tooth_code) }}"
        class="w-full border rounded px-3 py-2"
      >
    </div>

    {{-- Superficie --}}
    <div>
      <label class="block text-xs text-slate-500 mb-1">Superficie</label>
      <select name="surface" class="w-full border rounded px-3 py-2">
        @foreach([''=>'—','O'=>'O','M'=>'M','D'=>'D','B'=>'B','L'=>'L','I'=>'I'] as $k=>$v)
          <option value="{{ $k }}"
            @selected(old('surface',$treatment->surface)===$k)>
            {{ $v }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- Precio --}}
    <div>
      <label class="block text-xs text-slate-500 mb-1">Precio</label>
      <input
        type="number"
        step="0.01"
        min="0"
        name="price"
        value="{{ old('price',$treatment->price) }}"
        class="w-full border rounded px-3 py-2"
        required
      >
    </div>

    {{-- Estado --}}
    <div>
      <label class="block text-xs text-slate-500 mb-1">Estado</label>
      <select name="status" class="w-full border rounded px-3 py-2" required>
        @foreach(['planned'=>'Planificado','in_progress'=>'En curso','done'=>'Realizado','canceled'=>'Cancelado'] as $k=>$v)
          <option value="{{ $k }}"
            @selected(old('status',$treatment->status)===$k)>
            {{ $v }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- Notas --}}
    <div class="md:col-span-2">
      <label class="block text-xs text-slate-500 mb-1">Notas</label>
      <textarea
        name="notes"
        rows="3"
        class="w-full border rounded px-3 py-2"
      >{{ old('notes',$treatment->notes) }}</textarea>
    </div>

    <div class="md:col-span-2">
      <button class="btn btn-primary">Guardar</button>
    </div>
  </form>
</div>
@endsection
