@extends('layouts.app')
@section('title','Nuevo rol')
@section('content')
<form method="post" action="{{ route('admin.roles.store') }}" class="card space-y-3">
  @csrf
  @include('admin.roles._form', ['role'=>null])
  <div><button class="btn btn-primary">Guardar</button> <a class="btn btn-ghost" href="{{ route('admin.roles.index') }}">Cancelar</a></div>
</form>
@endsection
