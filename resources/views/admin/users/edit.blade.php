@extends('layouts.app')
@section('title','Editar usuario')

@section('content')
<form method="post" action="{{ route('admin.users.update',$user) }}" class="card space-y-3">
  @csrf @method('put')
  @include('admin.users._form', ['user'=>$user])
  <div>
    <button class="btn btn-primary">Actualizar</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Cancelar</a>
  </div>
</form>
@endsection
