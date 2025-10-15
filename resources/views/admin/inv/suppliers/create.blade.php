@extends('layouts.app')
@section('title','Nuevo proveedor')
@section('content')
  <div class="card">
    <form method="post" action="{{ route('admin.inv.suppliers.store') }}" class="grid md:grid-cols-2 gap-3">
      @csrf
      @include('admin.inv.suppliers.form-fields')
      <div class="md:col-span-2">
        <button class="btn">Guardar</button>
        <a href="{{ route('admin.inv.suppliers.index') }}" class="btn btn-ghost">Cancelar</a>
      </div>
    </form>
  </div>
@endsection
