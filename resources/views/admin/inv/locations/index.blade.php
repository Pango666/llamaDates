@extends('layouts.app')
@section('title','Ubicaciones')

@section('header-actions')
  <a href="{{ route('admin.inv.locations.create') }}" class="btn">+ Nueva</a>
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
          <th class="px-3 py-2">Código</th>
          <th class="px-3 py-2">Nombre</th>
          <th class="px-3 py-2">Principal</th>
          <th class="px-3 py-2">Estado</th>
          <th class="px-3 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach($locations as $l)
          <tr class="border-b">
            <td class="px-3 py-2">{{ $l->code ?: '—' }}</td>
            <td class="px-3 py-2">{{ $l->name }}</td>
            <td class="px-3 py-2">{{ $l->is_main ? 'Sí' : 'No' }}</td>
            <td class="px-3 py-2">
              <span class="badge {{ $l->active?'bg-emerald-100 text-emerald-700':'bg-slate-200 text-slate-600' }}">
                {{ $l->active ? 'Activa' : 'Inactiva' }}
              </span>
            </td>
            <td class="px-3 py-2 text-right">
              <a class="btn btn-ghost" href="{{ route('admin.inv.locations.edit',$l) }}">Editar</a>
              <form action="{{ route('admin.inv.locations.destroy',$l) }}" method="post" class="inline" onsubmit="return confirm('¿Eliminar ubicación?');">
                @csrf @method('DELETE')
                <button class="btn btn-danger">Eliminar</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="mt-3">{{ $locations->links() }}</div>
  </div>
@endsection
