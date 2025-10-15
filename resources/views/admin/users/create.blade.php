@extends('layouts.app')
@section('title','Nuevo usuario')

@section('content')
<form method="post" action="{{ route('admin.users.store') }}" class="card space-y-3">
  @csrf
  @include('admin.users._form', ['user'=>null])
  <div>
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Cancelar</a>
  </div>
</form>
@endsection
