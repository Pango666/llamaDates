@extends('layouts.app')
@section('title','Editar producto')

@section('content')
  <div class="card">
    <form method="post" action="{{ route('admin.inv.products.update',$product) }}" class="grid md:grid-cols-3 gap-3">
      @csrf @method('PUT')
      @include('admin.inv.products.form-fields')
      <div class="md:col-span-3">
        <button class="btn">Guardar cambios</button>
        <a href="{{ route('admin.inv.products.index') }}" class="btn btn-ghost">Volver</a>
      </div>
    </form>
  </div>
@endsection
