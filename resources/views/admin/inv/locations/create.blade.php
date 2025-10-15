@extends('layouts.app')
@section('title','Nueva ubicación')
@section('content')
  <div class="card">
    <form method="post" action="{{ route('admin.inv.locations.store') }}" class="grid md:grid-cols-2 gap-3">
      @csrf
      @include('admin.inv.locations.form-fields')
      <div class="md:col-span-2">
        <button class="btn">Guardar</button>
        <a href="{{ route('admin.inv.locations.index') }}" class="btn btn-ghost">Cancelar</a>
      </div>
    </form>
  </div>
@endsection
