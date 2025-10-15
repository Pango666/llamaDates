@extends('layouts.app')
@section('title','Nuevo permiso')
@section('content')
<form method="post" action="{{ route('admin.permissions.store') }}" class="card space-y-3">
  @csrf
  @include('admin.permissions._form', ['permission'=>null])
  <div><button class="btn btn-primary">Guardar</button> <a href="{{ route('admin.permissions.index') }}" class="btn btn-ghost">Cancelar</a></div>
</form>
@endsection
