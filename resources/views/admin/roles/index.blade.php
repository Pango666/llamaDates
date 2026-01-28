@extends('layouts.app')
@section('title', 'Gestión de Roles')

@section('header-actions')
  <a href="{{ route('admin.roles.create') }}" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Nuevo Rol
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
            Gestión de Roles
          </h1>
          <p class="text-sm text-slate-600 mt-1">Administre los roles del sistema, permisos y estado.</p>
        </div>
        <div class="text-right">
        <div class="text-right">
             <a href="{{ route('admin.roles.index') }}" class="text-3xl font-bold text-slate-800 hover:text-blue-600 transition-colors">{{ $totals['total'] }}</a>
             <div class="text-xs text-slate-500">Roles Totales</div>
        </div>
      </div>

      {{-- Metrics Grid --}}
      <div class="grid grid-cols-2 gap-4 mt-4">
          <a href="{{ route('admin.roles.index', ['status' => 'active']) }}" class="bg-emerald-50 rounded-lg p-3 border border-emerald-100 flex items-center justify-between hover:bg-emerald-100 transition-colors cursor-pointer group">
              <div>
                  <div class="text-xs text-emerald-800 font-medium group-hover:underline">Activos</div>
                  <div class="text-lg font-bold text-emerald-900">{{ $totals['active'] }}</div>
              </div>
              <div class="w-8 h-8 rounded-full bg-emerald-200 flex items-center justify-center text-emerald-700">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              </div>
          </a>
          
          <a href="{{ route('admin.roles.index', ['status' => 'inactive']) }}" class="bg-amber-50 rounded-lg p-3 border border-amber-100 flex items-center justify-between hover:bg-amber-100 transition-colors cursor-pointer group">
              <div>
                  <div class="text-xs text-amber-800 font-medium group-hover:underline">Inactivos</div>
                  <div class="text-lg font-bold text-amber-900">{{ $totals['inactive'] }}</div>
              </div>
              <div class="w-8 h-8 rounded-full bg-amber-200 flex items-center justify-center text-amber-700">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
              </div>
          </a>
      </div>
    </div>

    {{-- Filtros --}}
    <form method="get" class="card mb-6">
      <div class="flex flex-col md:flex-row gap-4 md:items-end">
        <div class="flex-1 space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Buscar Roles
          </label>
          <input 
            type="text" 
            name="q" 
            value="{{ $q }}" 
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            placeholder="Nombre o etiqueta del rol..."
          >
        </div>
        <div class="flex gap-2">
          <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Buscar
          </button>
          
          @if(request('status'))
               <span class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded self-center">Filtro: {{ request('status') }}</span>
          @endif

          @if($q)
            <a href="{{ route('admin.roles.index') }}" class="btn btn-ghost flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Limpiar
            </a>
          @elseif(request('status'))
            <a href="{{ route('admin.roles.index') }}" class="btn btn-ghost flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Limpiar Filtros
            </a>
          @endif
        </div>
      </div>
    </form>

    {{-- Tabla de roles --}}
    <div class="card p-0 overflow-hidden">
      @if($roles->count() > 0)
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr class="text-left">
                <th class="px-4 py-3 font-semibold text-slate-700">Rol</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Etiqueta</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Permisos</th>
                <th class="px-4 py-3 font-semibold text-slate-700 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              @foreach($roles as $role)
                <tr class="hover:bg-slate-50 transition-colors">
                  {{-- Nombre del rol --}}
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                      </div>
                      <div>
                        <p class="font-medium text-slate-800">{{ $role->name }}</p>
                        <p class="text-xs text-slate-500">
                          {{ $role->permissions_count }} permiso(s)
                        </p>
                      </div>
                    </div>
                  </td>

                  {{-- Etiqueta --}}
                  <td class="px-4 py-3">
                    @if($role->label)
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                        {{ $role->label }}
                      </span>
                    @else
                      <span class="text-slate-400 text-sm">—</span>
                    @endif
                  </td>

                  {{-- Conteo de permisos --}}
                  <td class="px-4 py-3">
                    <div class="flex flex-col gap-1 items-start">
                         <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $role->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                            {{ $role->is_active ? 'Activo' : 'Inactivo' }}
                         </span>
                         <span class="text-xs text-slate-500">{{ $role->permissions_count }} permisos</span>
                    </div>
                  </td>

                  {{-- Acciones --}}
                  <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                      <a 
                        href="{{ route('admin.roles.edit', $role) }}" 
                        class="btn btn-ghost flex items-center gap-1"
                        title="Editar rol"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar
                      </a>
                      <a 
                        href="{{ route('admin.roles.perms', $role) }}" 
                        class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-1"
                        title="Gestionar permisos"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Permisos
                      </a>
                      {{-- Toggle Action --}}
                      <form method="post" action="{{ route('admin.roles.toggle', $role) }}" class="inline">
                          @csrf
                          <button 
                            type="submit" 
                            class="btn btn-ghost flex items-center gap-1 {{ $role->is_active ? 'text-red-600 hover:bg-red-50' : 'text-emerald-600 hover:bg-emerald-50' }}"
                            title="{{ $role->is_active ? 'Desactivar' : 'Activar' }}"
                            onclick="return confirm('{{ $role->is_active ? '¿Desactivar rol? Esto suspenderá a todos los usuarios con este rol.' : '¿Activar rol?' }}')"
                          >
                            @if($role->is_active)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                Desactivar
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Activar
                            @endif
                          </button>
                      </form>
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
          <h3 class="text-lg font-medium text-slate-700 mb-2">No hay roles creados</h3>
          <p class="text-slate-500 mb-6">Comience creando el primer rol del sistema.</p>
          <a 
            href="{{ route('admin.roles.create') }}" 
            class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 inline-flex"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Crear Primer Rol
          </a>
        </div>
      @endif
    </div>

    {{-- Paginación --}}
    @if($roles->hasPages())
      <div class="mt-6">
        {{ $roles->links() }}
      </div>
    @endif
  </div>
@endsection