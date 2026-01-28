@extends('layouts.app')
@section('title', 'Gestión de Usuarios')

@section('header-actions')
  <a href="{{ route('admin.users.create') }}" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Nuevo Usuario
  </a>
@endsection

@section('content')
  <div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4 flex items-center justify-between">
        <div>
           <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
              </svg>
              Gestión de Usuarios
            </h1>
            <p class="text-sm text-slate-600 mt-1">Administre los usuarios del sistema y sus permisos.</p>
        </div>
        <div class="text-right">
             <a href="{{ route('admin.users.index') }}" class="text-3xl font-bold text-slate-800 hover:text-blue-600 transition-colors">{{ $totals['total'] }}</a>
             <div class="text-xs text-slate-500">Usuarios Totales</div>
        </div>
      </div>
      
      {{-- Metrics Grid --}}
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
          <a href="{{ route('admin.users.index', ['status' => 'active']) }}" class="bg-emerald-50 rounded-lg p-3 border border-emerald-100 flex items-center justify-between hover:bg-emerald-100 transition-colors cursor-pointer group">
              <div>
                  <div class="text-xs text-emerald-800 font-medium group-hover:underline">Activos</div>
                  <div class="text-lg font-bold text-emerald-900">{{ $totals['active'] }}</div>
              </div>
              <div class="w-8 h-8 rounded-full bg-emerald-200 flex items-center justify-center text-emerald-700">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 6a2 2 0 012-2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              </div>
          </a>
          
          <a href="{{ route('admin.users.index', ['status' => 'suspended']) }}" class="bg-amber-50 rounded-lg p-3 border border-amber-100 flex items-center justify-between hover:bg-amber-100 transition-colors cursor-pointer group">
              <div>
                  <div class="text-xs text-amber-800 font-medium group-hover:underline">Suspendidos</div>
                  <div class="text-lg font-bold text-amber-900">{{ $totals['suspended'] }}</div>
              </div>
              <div class="w-8 h-8 rounded-full bg-amber-200 flex items-center justify-center text-amber-700">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              </div>
          </a>

          <a href="{{ route('admin.users.index', ['role' => 'odontologo']) }}" class="bg-purple-50 rounded-lg p-3 border border-purple-100 flex items-center justify-between hover:bg-purple-100 transition-colors cursor-pointer group">
              <div>
                  <div class="text-xs text-purple-800 font-medium group-hover:underline">Odontólogos</div>
                  <div class="text-lg font-bold text-purple-900">{{ $totals['roles']['odontologo'] ?? 0 }}</div>
              </div>
              <div class="w-8 h-8 rounded-full bg-purple-200 flex items-center justify-center text-purple-700">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
          </a>
          
           <a href="{{ route('admin.users.index', ['role' => 'paciente']) }}" class="bg-blue-50 rounded-lg p-3 border border-blue-100 flex items-center justify-between hover:bg-blue-100 transition-colors cursor-pointer group">
              <div>
                  <div class="text-xs text-blue-800 font-medium group-hover:underline">Pacientes</div>
                  <div class="text-lg font-bold text-blue-900">{{ $totals['roles']['paciente'] ?? 0 }}</div>
              </div>
              <div class="w-8 h-8 rounded-full bg-blue-200 flex items-center justify-center text-blue-700">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
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
            Buscar Usuarios
          </label>
          <input 
            type="text" 
            name="q" 
            value="{{ $q }}" 
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            placeholder="Nombre o email del usuario..."
          >
        </div>
        <div class="flex gap-2">
          <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Buscar
          </button>
          
          <div class="hidden md:block">
               @if(request('status'))
                 <span class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded">Filtro: {{ request('status') }}</span>
               @endif
               @if(request('role'))
                 <span class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded">Rol: {{ request('role') }}</span>
               @endif
          </div>

          @if($q || request('status') || request('role'))
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Limpiar
            </a>
          @endif
        </div>
      </div>
    </form>

    {{-- Tabla de usuarios --}}
    <div class="card p-0 overflow-hidden">
      @if($users->count() > 0)
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr class="text-left">
                <th class="px-4 py-3 font-semibold text-slate-700">Usuario</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Email</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Rol Principal</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Estado</th>
                <th class="px-4 py-3 font-semibold text-slate-700 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              @foreach($users as $user)
                <tr class="hover:bg-slate-50 transition-colors">
                  {{-- Información del usuario --}}
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                      </div>
                      <div>
                        <p class="font-medium text-slate-800">{{ $user->name }}</p>
                        @if($user->phone)
                          <p class="text-xs text-slate-500">{{ $user->phone }}</p>
                        @endif
                      </div>
                    </div>
                  </td>

                  {{-- Email --}}
                  <td class="px-4 py-3 text-slate-600">
                    {{ $user->email }}
                  </td>

                  {{-- Rol Principal --}}
                  <td class="px-4 py-3">
                    @php
                      $roleColors = [
                        'admin' => 'bg-purple-100 text-purple-800',
                        'asistente' => 'bg-blue-100 text-blue-800',
                        'odontologo' => 'bg-emerald-100 text-emerald-800', 
                        'paciente' => 'bg-slate-100 text-slate-800'
                      ];
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $roleColors[$user->role] ?? 'bg-slate-100 text-slate-800' }}">
                      {{ ucfirst($user->role) }}
                    </span>
                  </td>

                  {{-- Estado --}}
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($user->status === 'active')
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        @else
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        @endif
                      </svg>
                      {{ $user->status === 'active' ? 'Activo' : 'Suspendido' }}
                    </span>
                  </td>

                  {{-- Acciones --}}
                  <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                      <a 
                        href="{{ route('admin.users.edit', $user) }}" 
                        class="btn btn-ghost flex items-center gap-1"
                        title="Editar usuario"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar
                      </a>
                      <form 
                        method="post" 
                        action="{{ route('admin.users.destroy', $user) }}" 
                        class="inline"
                        onsubmit="return confirm('¿Está seguro de eliminar este usuario? Esta acción no se puede deshacer.')"
                      >
                        @csrf @method('DELETE')
                        <button 
                          class="btn bg-red-600 text-white hover:bg-red-700 flex items-center gap-1"
                          title="Eliminar usuario"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                          </svg>
                          Eliminar
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
          </svg>
          <h3 class="text-lg font-medium text-slate-700 mb-2">No hay usuarios registrados</h3>
          <p class="text-slate-500 mb-6">Comience agregando el primer usuario del sistema.</p>
          <a 
            href="{{ route('admin.users.create') }}" 
            class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 inline-flex"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Agregar Primer Usuario
          </a>
        </div>
      @endif
    </div>

    {{-- Paginación --}}
    @if($users->hasPages())
      <div class="mt-6">
        {{ $users->links() }}
      </div>
    @endif
  </div>
@endsection