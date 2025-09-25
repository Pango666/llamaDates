@extends('layouts.app')
@section('title','Servicios')

@section('header-actions')
  <a href="{{ route('admin.services.create') }}" class="btn btn-primary">+ Nuevo servicio</a>
@endsection

@section('content')
  <form method="get" class="card mb-4">
    <div class="grid gap-3 md:grid-cols-3 md:items-end">
      <div class="md:col-span-2">
        <label class="block text-xs text-slate-500 mb-1">Buscar</label>
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Nombre"
               class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Estado</label>
        <select name="state" class="w-full border rounded px-3 py-2">
          <option value="all"      @selected(($state ?? 'all')==='all')>Todos</option>
          <option value="active"   @selected(($state ?? 'all')==='active')>Activos</option>
          <option value="inactive" @selected(($state ?? 'all')==='inactive')>Inactivos</option>
        </select>
      </div>
      <div class="md:col-span-3">
        <button class="btn btn-ghost">Filtrar</button>
        @if(($q ?? '')!=='' || ($state ?? 'all')!=='all')
          <a href="{{ route('admin.services') }}" class="btn btn-ghost">Limpiar</a>
        @endif
      </div>
    </div>
  </form>

  <div class="card p-0 overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-white border-b">
        <tr class="text-left">
          <th class="px-3 py-2">Servicio</th>
          <th class="px-3 py-2">Duración</th>
          <th class="px-3 py-2">Precio</th>
          <th class="px-3 py-2">Estado</th>
          <th class="px-3 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
      @forelse($services as $s)
        <tr class="border-b hover:bg-slate-50">
          <td class="px-3 py-2">{{ $s->name }}</td>
          <td class="px-3 py-2">{{ $s->duration_min }} min</td>
          <td class="px-3 py-2">{{ number_format($s->price,2) }}</td>
          <td class="px-3 py-2">
            <span class="badge {{ $s->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
              {{ $s->active ? 'Activo' : 'Inactivo' }}
            </span>
          </td>
          <td class="px-3 py-2">
            <div class="flex items-center justify-end gap-2">
              <a href="{{ route('admin.services.edit',$s) }}" class="btn btn-ghost">Editar</a>
              <form method="post" action="{{ route('admin.services.toggle',$s) }}">
                @csrf
                <button class="btn btn-ghost">{{ $s->active ? 'Desactivar' : 'Activar' }}</button>
              </form>
              <form method="post" action="{{ route('admin.services.destroy',$s) }}"
                    onsubmit="return confirm('¿Eliminar servicio?');">
                @csrf @method('DELETE')
                <button class="btn btn-danger">Eliminar</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">Sin resultados.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">{{ $services->links() }}</div>
@endsection
