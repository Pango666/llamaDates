@extends('layouts.app')
@section('title','Sillas')

@section('header-actions')
  <a href="{{ route('admin.chairs.usage') }}" class="btn btn-ghost">Ocupación por día</a>
  <a href="{{ route('admin.chairs.create') }}" class="btn btn-primary">+ Nueva silla</a>
@endsection

@section('content')
  <form method="get" class="card mb-4">
    <div class="grid gap-3 md:grid-cols-5 md:items-end">
      <div class="md:col-span-3">
        <label class="block text-xs text-slate-500 mb-1">Buscar</label>
        <input type="text" name="q" value="{{ $q }}" class="w-full border rounded px-3 py-2" placeholder="Nombre o turno">
      </div>
      <div class="md:col-span-2">
        <button class="btn btn-ghost">Filtrar</button>
        @if($q!=='')
          <a href="{{ route('admin.chairs.index') }}" class="btn btn-ghost">Limpiar</a>
        @endif
      </div>
    </div>
  </form>

  <div class="card p-0 overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-white border-b">
        <tr class="text-left">
          <th class="px-3 py-2">Nombre</th>
          <th class="px-3 py-2">Turno</th>
          <th class="px-3 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($chairs as $c)
          <tr class="border-b hover:bg-slate-50">
            <td class="px-3 py-2">{{ $c->name }}</td>
            <td class="px-3 py-2">{{ ucfirst($c->shift) }}</td>
            <td class="px-3 py-2">
              <div class="flex items-center justify-end gap-2">
                <a class="btn btn-ghost" href="{{ route('admin.chairs.edit',$c) }}">Editar</a>
                <form method="post" action="{{ route('admin.chairs.destroy',$c) }}" onsubmit="return confirm('¿Eliminar silla?');">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger">Eliminar</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="3" class="px-3 py-6 text-center text-slate-500">Sin sillas.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">{{ $chairs->links() }}</div>
@endsection
