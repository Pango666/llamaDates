@extends('layouts.app')
@section('title','Editar ubicación')
@section('content')
  <div class="card">
    <form method="post" action="{{ route('admin.inv.locations.update',$location) }}" class="grid md:grid-cols-2 gap-3">
      @csrf @method('PUT')
      @include('admin.inv.locations.form-fields')
      <div class="md:col-span-2">
        <button class="btn">Guardar cambios</button>
        <a href="{{ route('admin.inv.locations.index') }}" class="btn btn-ghost">Volver</a>
      </div>
    </form>
  </div>
@endsection
