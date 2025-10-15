@extends('layouts.app')
@section('title','Editar rol')
@section('content')
<form method="post" action="{{ route('admin.roles.update',$role) }}" class="card space-y-3">
  @csrf @method('put')
  @include('admin.roles._form', ['role'=>$role])
  <div><button class="btn btn-primary">Actualizar</button> <a class="btn btn-ghost" href="{{ route('admin.roles.index') }}">Cancelar</a></div>
</form>
@endsection
