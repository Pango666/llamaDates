@extends('layouts.app')
@section('title','Proveedores')

@section('header-actions')
  <a href="{{ route('admin.inv.suppliers.create') }}" class="btn">+ Nuevo</a>
@endsection

@section('content')
  <div class="card mb-3">
    <form method="get" class="flex gap-2">
      <input name="q" value="{{ $q }}" class="border rounded px-3 py-2" placeholder="Buscar...">
      <button class="btn">Filtrar</button>
    </form>
  </div>

  <div class="card overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="border-b">
        <tr class="text-left">
          <th class="px-3 py-2">Nombre</th>
          <th class="px-3 py-2">Email</th>
          <th class="px-3 py-2">Teléfono</th>
          <th class="px-3 py-2">Estado</th>
          <th class="px-3 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach($suppliers as $s)
          <tr class="border-b">
            <td class="px-3 py-2">{{ $s->name }}</td>
            <td class="px-3 py-2">{{ $s->email ?: '—' }}</td>
            <td class="px-3 py-2">{{ $s->phone ?: '—' }}</td>
            <td class="px-3 py-2">
              <span class="badge {{ $s->active?'bg-emerald-100 text-emerald-700':'bg-slate-200 text-slate-600' }}">
                {{ $s->active?'Activo':'Inactivo' }}
              </span>
            </td>
            <td class="px-3 py-2 text-right">
              <a class="btn btn-ghost" href="{{ route('admin.inv.suppliers.edit',$s) }}">Editar</a>
              <form action="{{ route('admin.inv.suppliers.destroy',$s) }}" method="post" class="inline" onsubmit="return confirm('¿Eliminar proveedor?');">
                @csrf @method('DELETE')
                <button class="btn btn-danger">Eliminar</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="mt-3">{{ $suppliers->links() }}</div>
  </div>
@endsection
