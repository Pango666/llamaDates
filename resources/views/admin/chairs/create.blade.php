@extends('layouts.app')
@section('title','Nueva silla')

@section('header-actions')
  <a href="{{ route('admin.chairs.index') }}" class="btn btn-ghost">Volver</a>
@endsection

@section('content')
<form method="post" action="{{ route('admin.chairs.store') }}" class="card space-y-3">
  @csrf
  <div>
    <label class="block text-xs text-slate-500 mb-1">Nombre</label>
    <input name="name" class="w-full border rounded px-3 py-2" required value="{{ old('name',$chair->name) }}">
  </div>
  <div>
    <label class="block text-xs text-slate-500 mb-1">Turno</label>
    <select name="shift" class="w-full border rounded px-3 py-2" required>
      @foreach(['mañana'=>'Mañana','tarde'=>'Tarde','completo'=>'Completo'] as $k=>$lbl)
        <option value="{{ $k }}" @selected(old('shift',$chair->shift)===$k)>{{ $lbl }}</option>
      @endforeach
    </select>
  </div>
  <button class="btn btn-primary">Guardar</button>
</form>
@endsection
