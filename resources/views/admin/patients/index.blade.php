@extends('layouts.app')
@section('title','Pacientes')

@section('header-actions')
  <a href="{{ route('admin.patients.create') }}" class="btn btn-primary">+ Nuevo paciente</a>
@endsection

@section('content')
  {{-- Filtro/Búsqueda --}}
  <form method="get" class="card mb-4">
    <div class="flex flex-col md:flex-row gap-3 md:items-end">
      <div class="flex-1">
        <label class="block text-xs text-slate-500 mb-1">Buscar</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Nombre, email, teléfono o CI"
               class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <button class="btn btn-ghost">Filtrar</button>
        @if($q !== '')
          <a class="btn btn-ghost" href="{{ route('admin.patients.index') }}">Limpiar</a>
        @endif
      </div>
    </div>
  </form>

  {{-- Tabla --}}
  <div class="card p-0">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-white border-b sticky top-0 z-10">
          <tr class="text-left">
            <th class="px-3 py-2">Nombre</th>
            <th class="px-3 py-2">Contacto</th>
            <th class="px-3 py-2">Nacimiento</th>
            <th class="px-3 py-2">Creado</th>
            <th class="px-3 py-2 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
        @forelse($patients as $p)
          @php $age = $p->birthdate ? \Carbon\Carbon::parse($p->birthdate)->age : null; @endphp
          <tr class="border-b hover:bg-slate-50">
            <td class="px-3 py-2 whitespace-nowrap">
              <a href="{{ route('admin.patients.show',$p) }}" class="font-medium hover:underline">
                {{ $p->last_name }}, {{ $p->first_name }}
              </a>
            </td>
            <td class="px-3 py-2">
              <div>{{ $p->email ?: '—' }}</div>
              <div class="text-xs text-slate-500">{{ $p->phone ?: '—' }}</div>
            </td>
            <td class="px-3 py-2 whitespace-nowrap">
              {{ $p->birthdate ?: '—' }}
              @if($age) <span class="text-xs text-slate-500">({{ $age }} años)</span> @endif
            </td>
            <td class="px-3 py-2 whitespace-nowrap">{{ $p->created_at?->format('Y-m-d') ?? '—' }}</td>
            <td class="px-3 py-2">
              <div class="flex items-center justify-end gap-2">
                <a href="{{ route('admin.patients.show',$p) }}" class="btn btn-ghost">Ver</a>
                <a href="{{ route('admin.patients.edit',$p) }}" class="btn btn-ghost">Editar</a>
                <a href="{{ route('admin.patients.record',$p) }}"
                   class="btn btn-ghost">Historia</a>
                <form method="post" action="{{ route('admin.patients.destroy',$p) }}"
                      onsubmit="return confirm('¿Eliminar paciente? Esta acción no se puede deshacer.');">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger">Eliminar</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td class="px-3 py-6 text-center text-slate-500" colspan="5">Sin resultados.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">{{ $patients->links() }}</div>
@endsection
