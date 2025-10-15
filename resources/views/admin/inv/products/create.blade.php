@extends('layouts.app')
@section('title','Nuevo producto')

@section('content')
  <div class="card">
    <form method="post" action="{{ route('admin.inv.products.store') }}" class="grid md:grid-cols-3 gap-3">
      @csrf
      @include('admin.inv.products.form-fields')
      <div class="md:col-span-3">
        <button class="btn">Guardar</button>
        <a href="{{ route('admin.inv.products.index') }}" class="btn btn-ghost">Cancelar</a>
      </div>
    </form>
  </div>
@endsection
