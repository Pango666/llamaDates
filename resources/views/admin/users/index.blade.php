@extends('layouts.app')
@section('title','Usuarios')

@section('header-actions')
  <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Nuevo usuario</a>
@endsection

@section('content')
  <form class="mb-3">
    <input name="q" value="{{ $q }}" class="border rounded px-3 py-2" placeholder="Buscar nombre o email">
    <button class="btn btn-ghost">Buscar</button>
  </form>

  <div class="card overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left">
        <th class="p-2">Nombre</th>
        <th class="p-2">Email</th>
        <th class="p-2">Rol(enum)</th>
        <th class="p-2">Estado</th>
        <th class="p-2"></th>
      </tr></thead>
      <tbody>
      @foreach($users as $u)
        <tr class="border-t">
          <td class="p-2">{{ $u->name }}</td>
          <td class="p-2">{{ $u->email }}</td>
          <td class="p-2">{{ $u->role }}</td>
          <td class="p-2">{{ $u->status }}</td>
          <td class="p-2">
            <a href="{{ route('admin.users.edit',$u) }}" class="btn btn-ghost">Editar</a>
            <form method="post" action="{{ route('admin.users.destroy',$u) }}" class="inline" onsubmit="return confirm('¿Eliminar?')">
              @csrf @method('delete')
              <button class="btn btn-danger">Eliminar</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-3">{{ $users->links() }}</div>
@endsection
