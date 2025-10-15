@extends('layouts.app')
@section('title','Roles')

@section('header-actions')
  <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">Nuevo rol</a>
@endsection

@section('content')
<form class="mb-3">
  <input name="q" value="{{ $q }}" class="border rounded px-3 py-2" placeholder="Buscar rol">
  <button class="btn btn-ghost">Buscar</button>
</form>

<div class="card overflow-x-auto">
<table class="w-full text-sm">
  <thead><tr class="text-left">
    <th class="p-2">Nombre</th><th class="p-2">Etiqueta</th><th class="p-2"></th>
  </tr></thead>
  <tbody>
  @foreach($roles as $r)
    <tr class="border-t">
      <td class="p-2">{{ $r->name }}</td>
      <td class="p-2">{{ $r->label }}</td>
      <td class="p-2">
        <a href="{{ route('admin.roles.edit',$r) }}" class="btn btn-ghost">Editar</a>
        <a href="{{ route('admin.roles.perms',$r) }}" class="btn btn-ghost">Permisos</a>
        <form method="post" action="{{ route('admin.roles.destroy',$r) }}" class="inline" onsubmit="return confirm('¿Eliminar rol?')">
          @csrf @method('delete')
          <button class="btn btn-danger">Eliminar</button>
        </form>
      </td>
    </tr>
  @endforeach
  </tbody>
</table>
</div>

<div class="mt-3">{{ $roles->links() }}</div>
@endsection
