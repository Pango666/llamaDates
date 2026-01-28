@extends('layouts.app')
@section('title','Gestión de Pacientes')

@section('header-actions')
  @can('patients.create')
  <a href="{{ route('admin.patients.create') }}" class="btn btn-primary flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Nuevo Paciente
  </a>
  @endcan
@endsection

@section('content')
  {{-- Estadísticas rápidas --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <a href="{{ route('admin.patients.index', ['status' => 'all']) }}" 
       class="card p-4 border-l-4 border-blue-500 hover:shadow-md transition-shadow cursor-pointer group">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm font-medium text-slate-500 group-hover:text-blue-600 transition-colors">Total Pacientes</div>
          <div class="text-2xl font-bold text-slate-800">{{ $counts['total'] }}</div>
        </div>
        <div class="p-2 bg-blue-50 rounded-lg group-hover:bg-blue-100 transition-colors">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </div>
      </div>
    </a>

    <a href="{{ route('admin.patients.index', ['status' => 'active']) }}" 
       class="card p-4 border-l-4 border-emerald-500 hover:shadow-md transition-shadow cursor-pointer group">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm font-medium text-slate-500 group-hover:text-emerald-600 transition-colors">Pacientes Activos</div>
          <div class="text-2xl font-bold text-slate-800">{{ $counts['active'] }}</div>
        </div>
        <div class="p-2 bg-emerald-50 rounded-lg group-hover:bg-emerald-100 transition-colors">
          <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>
    </a>

    <a href="{{ route('admin.patients.index', ['status' => 'inactive']) }}" 
       class="card p-4 border-l-4 border-red-500 hover:shadow-md transition-shadow cursor-pointer group">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm font-medium text-slate-500 group-hover:text-red-600 transition-colors">Pacientes Inactivos</div>
          <div class="text-2xl font-bold text-slate-800">{{ $counts['inactive'] }}</div>
        </div>
        <div class="p-2 bg-red-50 rounded-lg group-hover:bg-red-100 transition-colors">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
          </svg>
        </div>
      </div>
    </a>
  </div>

  {{-- Filtro/Búsqueda --}}
  <div class="card mb-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-semibold text-slate-700 flex items-center gap-2">
        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        Buscar Pacientes
      </h3>
      @if($q !== '' || request('status') !== 'active')
        <a href="{{ route('admin.patients.index') }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
            Limpiar filtros
        </a>
      @endif
    </div>
    
    <form method="get" class="flex flex-col md:flex-row gap-4 md:items-end">
      <div class="flex-1">
        <label class="block text-sm font-medium text-slate-700 mb-2">Término de búsqueda</label>
        <input 
          type="text" 
          name="q" 
          value="{{ $q }}" 
          placeholder="Nombre, apellido, email..."
          class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
        >
      </div>

      <div class="w-full md:w-48">
          <label class="block text-sm font-medium text-slate-700 mb-2">Estado</label>
          <select name="status" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
              <option value="active" @selected(request('status','active')=='active')>Activos</option>
              <option value="inactive" @selected(request('status')=='inactive')>Inactivos</option>
              <option value="all" @selected(request('status')=='all')>Todos</option>
          </select>
      </div>

      <div class="flex gap-2">
        <button class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
          Buscar
        </button>
      </div>
    </form>
  </div>

  {{-- Tabla --}}
  <div class="card p-0 overflow-hidden">
    <div class="p-4 border-b bg-slate-50">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold text-slate-700 flex items-center gap-2">
          <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
          Lista de Pacientes ({{ $patients->total() }})
        </h3>
        <div class="text-sm text-slate-500">
          Página {{ $patients->currentPage() }} de {{ $patients->lastPage() }}
        </div>
      </div>
    </div>
    
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-white border-b sticky top-0">
          <tr class="text-left">
            <th class="px-4 py-3 font-semibold text-slate-600 border-r">Paciente</th>
            <th class="px-4 py-3 font-semibold text-slate-600 border-r">Información de Contacto</th>
            <th class="px-4 py-3 font-semibold text-slate-600 border-r">Información Personal</th>
            <th class="px-4 py-3 font-semibold text-slate-600 border-r">Fecha de Registro</th>
            <th class="px-4 py-3 font-semibold text-slate-600 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
        @forelse($patients as $p)
          @php 
            $age = $p->birthdate ? \Carbon\Carbon::parse($p->birthdate)->age : null;
            $hasContact = $p->email || $p->phone;
          @endphp
          <tr class="border-b hover:bg-slate-50 transition-colors {{ !$p->is_active ? 'bg-slate-50 opacity-75' : '' }}">
            {{-- Columna Paciente --}}
            <td class="px-4 py-3 border-r">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 {{ $p->is_active ? 'bg-blue-100' : 'bg-slate-200' }} rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 {{ $p->is_active ? 'text-blue-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                </div>
                <div>
                  <a href="{{ route('admin.patients.show',$p) }}" 
                     class="font-semibold text-slate-800 hover:text-blue-600 hover:underline block">
                    {{ $p->last_name }}, {{ $p->first_name }}
                  </a>
                  @if($p->ci)
                    <div class="text-xs text-slate-500 mt-1">CI: {{ $p->ci }}</div>
                  @endif
                  @if(!$p->is_active)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-800 mt-1">INACTIVO</span>
                  @endif
                </div>
              </div>
            </td>

            {{-- Columna Contacto --}}
            <td class="px-4 py-3 border-r">
              <div class="space-y-1">
                @if($p->email)
                  <div class="flex items-center gap-2 text-slate-700">
                    <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-xs">{{ $p->email }}</span>
                  </div>
                @endif
                @if($p->phone)
                  <div class="flex items-center gap-2 text-slate-700">
                    <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span class="text-xs">{{ $p->phone }}</span>
                  </div>
                @endif
                @if(!$hasContact)
                  <span class="text-xs text-slate-400 italic">Sin información de contacto</span>
                @endif
              </div>
            </td>

            {{-- Columna Información Personal --}}
            <td class="px-4 py-3 border-r">
              <div class="space-y-1">
                @if($p->birthdate)
                  <div class="text-slate-700">
                    <div class="text-sm">{{ \Carbon\Carbon::parse($p->birthdate)->format('d/m/Y') }}</div>
                    <div class="text-xs text-slate-500">{{ $age }} años</div>
                  </div>
                @else
                  <span class="text-xs text-slate-400 italic">Sin fecha de nacimiento</span>
                @endif
              </div>
            </td>

            {{-- Columna Registro --}}
            <td class="px-4 py-3 border-r">
              <div class="text-slate-700">
                <div class="text-sm">{{ $p->created_at?->format('d/m/Y') ?? '—' }}</div>
                <div class="text-xs text-slate-500">
                  {{ $p->created_at?->diffForHumans() ?? '' }}
                </div>
              </div>
            </td>

            {{-- Columna Acciones --}}
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-1">
                <a href="{{ route('admin.patients.show',$p) }}" 
                   class="btn btn-ghost text-xs p-2 hover:bg-blue-50 hover:text-blue-600"
                   title="Ver detalles">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                </a>
                
                @if(auth()->user()->hasAnyPermission(['patients.manage', 'patients.edit', 'patients.update']))
                <a href="{{ route('admin.patients.edit',$p)}}" 
                   class="btn btn-ghost text-xs p-2 hover:bg-green-50 hover:text-green-600"
                   title="Editar paciente">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </a>
                @endif
                
                @if(auth()->user()->hasAnyPermission(['patients.history.view', 'medical_history.manage']))
                <a href="{{ route('admin.patients.record',$p) }}"
                   class="btn btn-ghost text-xs p-2 hover:bg-orange-50 hover:text-orange-600"
                   title="Historia clínica">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                </a>
                @endif
                
                @if(auth()->user()->hasAnyPermission(['patients.manage', 'patients.destroy']))
                <form method="post" action="{{ route('admin.patients.toggle',$p) }}" class="inline">
                  @csrf
                  @if($p->is_active)
                    <button type="submit" 
                            onclick="return confirm('¿Desactivar paciente? Esto también suspenderá su usuario de portal.');"
                            class="btn btn-ghost text-xs p-2 hover:bg-red-50 hover:text-red-600"
                            title="Desactivar paciente">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </button>
                  @else
                    <button type="submit" 
                            onclick="return confirm('¿Reactivar paciente?');"
                            class="btn btn-ghost text-xs p-2 hover:bg-emerald-50 hover:text-emerald-600"
                            title="Reactivar paciente">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                  @endif
                </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-8 text-center text-slate-500">
              <div class="flex flex-col items-center gap-3">
                <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <div>
                  <div class="font-medium text-slate-600">No se encontraron pacientes</div>
                  <div class="text-sm text-slate-500 mt-1">
                    @if($q !== '')
                      No hay resultados para "{{ $q }}". Intente con otros términos de búsqueda.
                    @else
                      No hay pacientes registrados en el sistema.
                    @endif
                  </div>
                </div>
              </div>
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Paginación --}}
  @if($patients->hasPages())
    <div class="mt-6 flex items-center justify-between">
      <div class="text-sm text-slate-600">
        Mostrando {{ $patients->firstItem() ?? 0 }} - {{ $patients->lastItem() ?? 0 }} de {{ $patients->total() }} pacientes
      </div>
      <div class="bg-white rounded-lg border border-slate-200">
        {{ $patients->links() }}
      </div>
    </div>
  @endif
@endsection