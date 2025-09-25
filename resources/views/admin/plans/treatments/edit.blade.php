@extends('layouts.app')
@section('title','Editar tratamiento')

@section('header-actions')
  <a href="{{ route('admin.plans.edit',$plan) }}" class="btn btn-ghost">Volver al plan</a>
@endsection

@section('content')
<div class="card max-w-3xl">
  <form method="post" action="{{ route('admin.plans.treatments.update', [$plan,$treatment]) }}" class="grid md:grid-cols-2 gap-4">
    @csrf @method('PUT')

    <div class="md:col-span-2">
      <label class="block text-xs text-slate-500 mb-1">Servicio</label>
      <select name="service_id" class="w-full border rounded px-3 py-2" required>
        @foreach($services as $s)
          <option value="{{ $s->id }}" @selected(old('service_id',$treatment->service_id)==$s->id)>
            {{ $s->name }}
          </option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="block text-xs text-slate-500 mb-1">Pieza</label>
      <input name="tooth_code" value="{{ old('tooth_code',$treatment->tooth_code) }}" class="w-full border rounded px-3 py-2">
    </div>

    <div>
      <label class="block text-xs text-slate-500 mb-1">Superficie</label>
      <select name="surface" class="w-full border rounded px-3 py-2">
        @foreach([''=>'—','O'=>'O','M'=>'M','D'=>'D','B'=>'B','L'=>'L'] as $k=>$v)
          <option value="{{ $k }}" @selected(old('surface',$treatment->surface)===$k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="block text-xs text-slate-500 mb-1">Precio</label>
      <input type="number" step="0.01" name="price" value="{{ old('price',$treatment->price) }}" class="w-full border rounded px-3 py-2" required>
    </div>

    <div>
      <label class="block text-xs text-slate-500 mb-1">Estado</label>
      <select name="status" class="w-full border rounded px-3 py-2" required>
        @foreach(['planned'=>'Planificado','in_progress'=>'En curso','done'=>'Realizado','canceled'=>'Cancelado'] as $k=>$v)
          <option value="{{ $k }}" @selected(old('status',$treatment->status)===$k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>

    <div class="md:col-span-2">
      <label class="block text-xs text-slate-500 mb-1">Notas</label>
      <textarea name="notes" rows="3" class="w-full border rounded px-3 py-2">{{ old('notes',$treatment->notes) }}</textarea>
    </div>

    <div class="md:col-span-2">
      <button class="btn btn-primary">Guardar</button>
    </div>
  </form>
</div>
@endsection
