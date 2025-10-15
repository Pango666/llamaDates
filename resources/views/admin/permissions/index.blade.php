@extends('layouts.app')
@section('title','Permisos')

@section('header-actions')
  <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">Nuevo permiso</a>
@endsection

@section('content')
<form class="mb-3">
  <input name="q" value="{{ $q }}" class="border rounded px-3 py-2" placeholder="Buscar permiso">
  <button class="btn btn-ghost">Buscar</button>
</form>

<div class="card overflow-x-auto">
<table class="w-full text-sm">
  <thead><tr class="text-left">
    <th class="p-2">Nombre</th><th class="p-2">Etiqueta</th><th class="p-2"></th>
  </tr></thead>
  <tbody>
  @foreach($perms as $p)
    <tr class="border-t">
      <td class="p-2">{{ $p->name }}</td>
      <td class="p-2">{{ $p->label }}</td>
      <td class="p-2">
        <a href="{{ route('admin.permissions.edit',$p) }}" class="btn btn-ghost">Editar</a>
        <form method="post" action="{{ route('admin.permissions.destroy',$p) }}" class="inline" onsubmit="return confirm('¿Eliminar permiso?')">
          @csrf @method('delete')
          <button class="btn btn-danger">Eliminar</button>
        </form>
      </td>
    </tr>
  @endforeach
  </tbody>
</table>
</div>

<div class="mt-3">{{ $perms->links() }}</div>
@endsection
