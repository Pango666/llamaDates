@extends('layouts.app')
@section('title','Consultorios')

@section('header-actions')
  <a href="{{ route('admin.chairs.create') }}" class="btn btn-primary">+ Nuevo Consultorio</a>
@endsection

@section('content')
  <form method="get" class="card mb-3">
    <label class="block text-xs text-slate-500 mb-1">Buscar</label>
    <div class="flex gap-2">
      <input type="text" name="q" value="{{ $q }}" class="border rounded px-3 py-2 w-full" placeholder="Nombre…">
      <button class="btn btn-ghost">Filtrar</button>
      @if($q!=='')
        <a href="{{ route('admin.chairs') }}" class="btn btn-ghost">Limpiar</a>
      @endif
    </div>
  </form>

  <div class="card p-0 overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="border-b bg-white">
        <tr class="text-left">
          <th class="px-3 py-2">Nombre</th>
          <th class="px-3 py-2">Turno</th>
          <th class="px-3 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($chairs as $c)
          <tr class="border-b">
            <td class="px-3 py-2">{{ $c->name }}</td>
            <td class="px-3 py-2">
              <span class="badge bg-slate-100 text-slate-700">{{ ucfirst($c->shift) }}</span>
            </td>
            <td class="px-3 py-2">
              <div class="flex items-center justify-end gap-2">
                <a href="{{ route('admin.chairs.edit',$c) }}" class="btn btn-ghost">Editar</a>
                <form method="post" action="{{ route('admin.chairs.destroy',$c) }}" onsubmit="return confirm('¿Eliminar consultorio?');">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger">Eliminar</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="3" class="px-3 py-6 text-center text-slate-500">Sin consultorios.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">{{ $chairs->links() }}</div>
@endsection
