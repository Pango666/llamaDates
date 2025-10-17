@extends('layouts.app')
@section('title', 'Horarios de Odontólogos')

@section('header-actions')
  <a href="{{ route('admin.dentists') }}" class="btn btn-ghost flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
    </svg>
    Ver Odontólogos
  </a>
@endsection

@section('content')
  <div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Gestión de Horarios
        </h1>
        <p class="text-sm text-slate-600 mt-1">Configure los horarios de atención de cada odontólogo.</p>
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
            Buscar Odontólogo
          </label>
          <input 
            type="text" 
            name="q" 
            value="{{ $q }}" 
            placeholder="Nombre o especialidad..."
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
        </div>
        
        <div class="flex gap-2">
          <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Filtrar
          </button>
          
          @if($q !== '')
            <a href="{{ route('admin.schedules') }}" class="btn btn-ghost flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Limpiar
            </a>
          @endif
        </div>
      </div>
    </form>

    {{-- Estadísticas rápidas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="card bg-blue-50 border-blue-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-blue-100 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-blue-800">Total Odontólogos</p>
            <p class="text-2xl font-bold text-blue-900">{{ $dentists->total() }}</p>
          </div>
        </div>
      </div>

      <div class="card bg-green-50 border-green-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-green-100 rounded-lg">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-green-800">Con Horario</p>
            <p class="text-2xl font-bold text-green-900">
              {{ $dentists->where('blocks_count', '>', 0)->count() }}
            </p>
          </div>
        </div>
      </div>

      <div class="card bg-amber-50 border-amber-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-amber-100 rounded-lg">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-amber-800">Sin Configurar</p>
            <p class="text-2xl font-bold text-amber-900">
              {{ $dentists->where('blocks_count', 0)->count() }}
            </p>
          </div>
        </div>
      </div>
    </div>

    {{-- Tabla de horarios --}}
    <div class="card p-0 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-left">
              <th class="px-4 py-3 font-semibold text-slate-700">Odontólogo</th>
              <th class="px-4 py-3 font-semibold text-slate-700">Especialidad</th>
              <th class="px-4 py-3 font-semibold text-slate-700">Días de Atención</th>
              <th class="px-4 py-3 font-semibold text-slate-700">Bloques</th>
              <th class="px-4 py-3 font-semibold text-slate-700 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            @forelse($dentists as $d)
              @php
                $days = $daysByDentist[$d->id] ?? [];
                $hasSchedule = $d->blocks_count > 0;
              @endphp
              <tr class="hover:bg-slate-50 transition-colors">
                {{-- Odontólogo --}}
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                      <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-slate-800">{{ $d->name }}</p>
                      @if($d->chair)
                        <p class="text-xs text-slate-500">Sillón: {{ $d->chair->name }}</p>
                      @endif
                    </div>
                  </div>
                </td>

                {{-- Especialidad --}}
                <td class="px-4 py-3">
                  @if($d->specialty)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ $d->specialty }}
                    </span>
                  @else
                    <span class="text-slate-500">—</span>
                  @endif
                </td>

                {{-- Días configurados --}}
                <td class="px-4 py-3">
                  @if(count($days) > 0)
                    <div class="flex flex-wrap gap-1">
                      @foreach($days as $dy)
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                          {{ $dayLabels[$dy] }}
                        </span>
                      @endforeach
                    </div>
                  @else
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-slate-100 text-slate-600">
                      Sin configurar
                    </span>
                  @endif
                </td>

                {{-- Bloques --}}
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    @if($hasSchedule)
                      <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                      </svg>
                      <span class="font-medium text-slate-800">{{ $d->blocks_count }}</span>
                      <span class="text-xs text-slate-500">bloques</span>
                    @else
                      <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                      <span class="text-slate-500">Sin bloques</span>
                    @endif
                  </div>
                </td>

                {{-- Acciones --}}
                <td class="px-4 py-3">
                  <div class="flex items-center justify-end gap-2">
                    <a 
                      href="{{ route('admin.schedules.edit', $d) }}" 
                      class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 transition-colors"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                      </svg>
                      Configurar
                    </a>
                    
                    {{-- @if($hasSchedule)
                      <a 
                        href="{{ route('admin.schedules.show', $d) }}" 
                        class="btn btn-ghost flex items-center gap-2 transition-colors"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Ver
                      </a>
                    @endif --}}
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-4 py-8 text-center">
                  <div class="flex flex-col items-center justify-center text-slate-500">
                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-lg font-medium mb-1">No se encontraron odontólogos</p>
                    <p class="text-sm">No hay resultados que coincidan con tu búsqueda.</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Paginación --}}
    @if($dentists->hasPages())
      <div class="mt-6">
        {{ $dentists->links() }}
      </div>
    @endif
  </div>
@endsection