@extends('layouts.app')
@section('title','Odontólogos')

@section('header-actions')
  <a href="{{ route('admin.dentists.create') }}" class="btn btn-primary">+ Nuevo odontólogo</a>
@endsection

@section('content')
  <form method="get" class="card mb-4">
    <div class="flex flex-col md:flex-row gap-3 md:items-end">
      <div class="flex-1">
        <label class="block text-xs text-slate-500 mb-1">Buscar</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Nombre o especialidad"
               class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <button class="btn btn-ghost">Filtrar</button>
        @if($q!=='')
          <a class="btn btn-ghost" href="{{ route('admin.dentists') }}">Limpiar</a>
        @endif
      </div>
    </div>
  </form>

  <div class="card p-0 overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-white border-b">
        <tr class="text-left">
          <th class="px-3 py-2">Nombre</th>
          <th class="px-3 py-2">Especialidad</th>
          <th class="px-3 py-2">Sillón</th>
          <th class="px-3 py-2">Próximas</th>
          <th class="px-3 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($dentists as $d)
          <tr class="border-b hover:bg-slate-50">
            <td class="px-3 py-2">
              <a class="font-medium hover:underline" href="{{ route('admin.dentists.show',$d) }}">{{ $d->name }}</a>
            </td>
            <td class="px-3 py-2">{{ $d->specialty ?: '—' }}</td>
            <td class="px-3 py-2">{{ $d->chair->name ?? '—' }}</td>
            <td class="px-3 py-2">{{ $nextCounts[$d->id] ?? 0 }}</td>
            <td class="px-3 py-2">
              <div class="flex items-center justify-end gap-2">
                <a class="btn btn-ghost" href="{{ route('admin.dentists.show',$d) }}">Ver</a>
                <a class="btn btn-ghost" href="{{ route('admin.dentists.edit',$d) }}">Editar</a>
                <form method="post" action="{{ route('admin.dentists.destroy',$d) }}"
                      onsubmit="return confirm('¿Eliminar odontólogo?');">
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

  <div class="mt-3">{{ $dentists->links() }}</div>
@endsection
