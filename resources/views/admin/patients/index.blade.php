@extends('layouts.app')
@section('title','Gestión de Pacientes')

@section('header-actions')
  <a href="{{ route('admin.patients.create') }}" class="btn btn-primary flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Nuevo Paciente
  </a>
@endsection

@section('content')
  {{-- Estadísticas rápidas --}}
  @php
    $totalPatients = $patients->total();
    $withBirthdate = $patients->whereNotNull('birthdate')->count();
    $withEmail = $patients->whereNotNull('email')->count();
    $withPhone = $patients->whereNotNull('phone')->count();
  @endphp
  
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <div class="card text-center p-4 bg-blue-50 border-blue-200">
      <div class="text-2xl font-bold text-blue-600">{{ $totalPatients }}</div>
      <div class="text-sm text-blue-700 font-medium">Total Pacientes</div>
    </div>
    
    <div class="card text-center p-4 bg-green-50 border-green-200">
      <div class="text-2xl font-bold text-green-600">{{ $withBirthdate }}</div>
      <div class="text-sm text-green-700 font-medium">Con Fecha Nac.</div>
    </div>
    
    <div class="card text-center p-4 bg-orange-50 border-orange-200">
      <div class="text-2xl font-bold text-orange-600">{{ $withEmail }}</div>
      <div class="text-sm text-orange-700 font-medium">Con Email</div>
    </div>
    
    <div class="card text-center p-4 bg-purple-50 border-purple-200">
      <div class="text-2xl font-bold text-purple-600">{{ $withPhone }}</div>
      <div class="text-sm text-purple-700 font-medium">Con Teléfono</div>
    </div>
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
      @if($q !== '')
        <a href="{{ route('admin.patients.index') }}" class="btn btn-ghost text-sm flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
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
          placeholder="Nombre, apellido, email, teléfono o cédula..."
          class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
        >
        <p class="text-xs text-slate-500 mt-1">Busque por cualquier dato del paciente</p>
      </div>
      <div class="flex gap-2">
        <button class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
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
          Lista de Pacientes ({{ $totalPatients }})
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
            $isComplete = $p->birthdate && $p->email && $p->phone;
          @endphp
          <tr class="border-b hover:bg-slate-50 transition-colors">
            {{-- Columna Paciente --}}
            <td class="px-4 py-3 border-r">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                
                <a href="{{ route('admin.patients.edit',$p)}}" 
                   class="btn btn-ghost text-xs p-2 hover:bg-green-50 hover:text-green-600"
                   title="Editar paciente">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </a>
                
                <a href="{{ route('admin.patients.record',$p) }}"
                   class="btn btn-ghost text-xs p-2 hover:bg-orange-50 hover:text-orange-600"
                   title="Historia clínica">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                </a>
                
                <form method="post" action="{{ route('admin.patients.destroy',$p) }}"
                      onsubmit="return confirm('¿Está seguro de eliminar este paciente? Esta acción no se puede deshacer.');"
                      class="inline">
                  @csrf @method('DELETE')
                  <button type="submit" 
                          class="btn btn-ghost text-xs p-2 hover:bg-red-50 hover:text-red-600"
                          title="Eliminar paciente">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </form>
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
        Mostrando {{ $patients->firstItem() ?? 0 }} - {{ $patients->lastItem() ?? 0 }} de {{ $totalPatients }} pacientes
      </div>
      <div class="bg-white rounded-lg border border-slate-200">
        {{ $patients->links() }}
      </div>
    </div>
  @endif
@endsection