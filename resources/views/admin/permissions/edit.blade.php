@extends('layouts.app')
@section('title','Editar permiso')
@section('content')
<form method="post" action="{{ route('admin.permissions.update',$permission) }}" class="card space-y-3">
  @csrf @method('put')
  @include('admin.permissions._form', ['permission'=>$permission])
  <div><button class="btn btn-primary">Actualizar</button> <a href="{{ route('admin.permissions.index') }}" class="btn btn-ghost">Cancelar</a></div>
</form>
@endsection
