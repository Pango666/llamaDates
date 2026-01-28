@extends('layouts.app')
@section('title','Unidades de Presentación')

@section('header-actions')
  <a href="{{ route('admin.inv.presentation_units.create') }}" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Nueva Unidad
  </a>
@endsection

@section('content')
  <div class="max-w-5xl mx-auto">

    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
          </svg>
          Unidades de Presentación
        </h1>
        <p class="text-sm text-slate-600 mt-1">
          Tipos de empaque: Caja, Frasco, Blister, Botella, etc.
        </p>
      </div>
    </div>

    {{-- Estadísticas Rápidas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
       {{-- Total --}}
       <div class="card bg-blue-50 border-blue-200">
         <a href="{{ route('admin.inv.presentation_units.index') }}" class="flex items-center gap-3 hover:opacity-75 transition-opacity">
           <div class="p-2 bg-blue-100 rounded-lg">
             <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
             </svg>
           </div>
           <div>
             <p class="text-sm font-medium text-blue-800">Total</p>
             <p class="text-2xl font-bold text-blue-900">{{ $units->total() }}</p>
           </div>
         </a>
       </div>

       {{-- Activas --}}
       <div class="card bg-emerald-50 border-emerald-200">
         <a href="{{ route('admin.inv.presentation_units.index', ['active' => '1']) }}" class="flex items-center gap-3 hover:opacity-75 transition-opacity">
           <div class="p-2 bg-emerald-100 rounded-lg">
             <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
             </svg>
           </div>
           <div>
             <p class="text-sm font-medium text-emerald-800">Activas</p>
             <p class="text-2xl font-bold text-emerald-900">{{ $activeCount }}</p>
           </div>
         </a>
       </div>

       {{-- Inactivas --}}
       <div class="card bg-slate-50 border-slate-200">
         <a href="{{ route('admin.inv.presentation_units.index', ['active' => '0']) }}" class="flex items-center gap-3 hover:opacity-75 transition-opacity">
           <div class="p-2 bg-slate-200 rounded-lg">
             <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
             </svg>
           </div>
           <div>
             <p class="text-sm font-medium text-slate-800">Inactivas</p>
             <p class="text-2xl font-bold text-slate-900">{{ $inactiveCount }}</p>
           </div>
         </a>
       </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-6">
      <form method="get" class="grid gap-4 md:grid-cols-4 md:items-end">
        <div class="space-y-2 md:col-span-2">
          <label class="block text-sm font-medium text-slate-700">Buscar</label>
          <input
            type="text"
            name="q"
            value="{{ $q }}"
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            placeholder="Nombre o nombre corto..."
          >
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700">Estado</label>
            <select name="active" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                <option value="all" @selected($active == 'all')>Todos</option>
                <option value="1" @selected($active == '1')>Activos</option>
                <option value="0" @selected($active == '0')>Inactivos</option>
            </select>
        </div>

        <div class="flex gap-2">
          <button class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Filtrar
          </button>
          @if($q !== '' || $active !== 'all')
            <a href="{{ route('admin.inv.presentation_units.index') }}" class="btn btn-ghost flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Limpiar
            </a>
          @endif
        </div>
      </form>
    </div>

    {{-- Tabla --}}
    <div class="card p-0 overflow-hidden">
      @if($units->count())
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Nombre</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Abreviatura</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Descripción</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Estado</th>
                <th class="px-4 py-3 text-right font-semibold text-slate-700">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              @foreach($units as $unit)
                <tr class="hover:bg-slate-50">
                  <td class="px-4 py-3 font-medium text-slate-800">
                    {{ $unit->name }}
                  </td>
                  <td class="px-4 py-3">
                    <code class="px-2 py-1 bg-slate-100 rounded text-xs text-slate-700">
                      {{ $unit->short_name }}
                    </code>
                  </td>
                  <td class="px-4 py-3 text-slate-600 truncate max-w-xs">
                    {{ $unit->description ?? '-' }}
                  </td>
                  <td class="px-4 py-3">
                    @if($unit->is_active)
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                        Activa
                      </span>
                    @else
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-200 text-slate-600">
                        Inactiva
                      </span>
                    @endif
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                      <a href="{{ route('admin.inv.presentation_units.edit', $unit) }}" class="btn btn-ghost text-sm flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar
                      </a>
                      
                      {{-- Toggle Form --}}
                      <form method="post" action="{{ route('admin.inv.presentation_units.toggle', $unit) }}">
                        @csrf 
                        @if($unit->is_active)
                           <button class="btn bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-red-600 text-sm flex items-center gap-1" title="Desactivar">
                               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                               Desactivar
                           </button>
                        @else
                           <button class="btn bg-emerald-100 text-emerald-700 hover:bg-emerald-200 text-sm flex items-center gap-1" title="Activar">
                               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                               Activar
                           </button>
                        @endif
                      </form>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="text-center py-10">
          <p class="text-slate-600">No hay unidades de presentación registradas.</p>
        </div>
      @endif
    </div>

    @if($units->hasPages())
      <div class="mt-4">
        {{ $units->links() }}
      </div>
    @endif
  </div>
@endsection
