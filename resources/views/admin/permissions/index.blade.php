@extends('layouts.app')
@section('title', 'Gestión de Permisos')

@section('header-actions')
  <a href="{{ route('admin.permissions.create') }}" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Nuevo Permiso
  </a>
@endsection

@section('content')
  <div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4 flex items-center justify-between">
        <div>
           <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
              Gestión de Permisos
            </h1>
            <p class="text-sm text-slate-600 mt-1">Listado de permisos del sistema (Solo Lectura/Edición).</p>
        </div>
        <div class="text-right">
             <div class="text-3xl font-bold text-slate-800">{{ $total }}</div>
             <div class="text-xs text-slate-500">Permisos Totales</div>
        </div>
      </div>
    </div>

    {{-- Filtros --}}
    <form method="get" class="card mb-6">
      <div class="flex flex-col md:flex-row gap-4 md:items-end">
        <div class="flex-1">
          <label class="block text-sm font-medium text-slate-700 mb-2 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Buscar Permiso
          </label>
          <input 
            type="text" 
            name="q" 
            value="{{ $q }}" 
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            placeholder="Buscar por nombre o etiqueta..."
          >
        </div>
        <div class="flex gap-2">
          <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Buscar
          </button>
          @if($q)
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-ghost flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Limpiar
            </a>
          @endif
        </div>
      </div>
    </form>

    {{-- Tabla de permisos --}}
    <div class="card p-0 overflow-hidden">
      @if($perms->count() > 0)
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr class="text-left">
                <th class="px-4 py-3 font-semibold text-slate-700">Nombre (Clave)</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Etiqueta</th>
                <th class="px-4 py-3 font-semibold text-slate-700 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              @foreach($perms as $permission)
                <tr class="hover:bg-slate-50 transition-colors">
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                      </div>
                      <div>
                        <code class="text-sm font-medium text-slate-800 bg-slate-100 px-2 py-1 rounded">
                          {{ $permission->name }}
                        </code>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <span class="text-slate-700">{{ $permission->label }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                      <a 
                        href="{{ route('admin.permissions.edit', $permission) }}" 
                        class="btn btn-ghost flex items-center gap-1"
                        title="Editar permiso"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar
                      </a>
                      {{-- DELETE REMOVED AS PER USER REQUEST --}}
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        {{-- Empty State --}}
        <div class="text-center py-12">
          <svg class="w-16 h-16 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
          <h3 class="text-lg font-medium text-slate-700 mb-2">
            @if($q)
              No se encontraron permisos
            @else
              No hay permisos creados
            @endif
          </h3>
          <p class="text-slate-500 mb-6">
            @if($q)
              No hay resultados que coincidan con tu búsqueda.
            @else
              Comience creando su primer permiso del sistema.
            @endif
          </p>
          <a 
            href="{{ route('admin.permissions.create') }}" 
            class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 inline-flex"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Crear Primer Permiso
          </a>
        </div>
      @endif
    </div>

    {{-- Paginación --}}
    @if($perms->hasPages())
      <div class="mt-6">
        {{ $perms->links() }}
      </div>
    @endif
  </div>
@endsection