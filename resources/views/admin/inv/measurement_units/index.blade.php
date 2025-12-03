@extends('layouts.app')
@section('title','Unidades de Medida')

@section('header-actions')
  <a href="{{ route('admin.inv.measurement_units.create') }}" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h8"/>
          </svg>
          Unidades de Medida
        </h1>
        <p class="text-sm text-slate-600 mt-1">
          Define unidades de concentración: mg, ml, %, UI, etc.
        </p>
      </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-6">
      <form method="get" class="grid gap-4 md:grid-cols-4 md:items-end">
        <div class="space-y-2 md:col-span-3">
          <label class="block text-sm font-medium text-slate-700">
            Buscar
          </label>
          <input
            type="text"
            name="q"
            value="{{ $q }}"
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            placeholder="Nombre o símbolo..."
          >
        </div>
        <div class="flex gap-2">
          <button class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Filtrar
          </button>
          @if($q !== '')
            <a href="{{ route('admin.inv.measurement_units.index') }}" class="btn btn-ghost flex items-center gap-2">
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
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Símbolo</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Tipo</th>
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
                      {{ $unit->symbol }}
                    </code>
                  </td>
                  <td class="px-4 py-3 text-slate-600">
                    @php
                      $label = match($unit->type) {
                        'mass'   => 'Masa (mg, g, kg)',
                        'volume' => 'Volumen (ml, L)',
                        'ratio'  => 'Relación / %',
                        'other'  => 'Otra',
                        default  => 'Sin tipo'
                      };
                    @endphp
                    {{ $label }}
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
                      <a href="{{ route('admin.inv.measurement_units.edit', $unit) }}" class="btn btn-ghost text-sm flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar
                      </a>
                      <form method="post" action="{{ route('admin.inv.measurement_units.destroy', $unit) }}" onsubmit="return confirm('¿Eliminar unidad de medida?');">
                        @csrf @method('DELETE')
                        <button class="btn bg-red-600 text-white hover:bg-red-700 text-sm flex items-center gap-1">
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
        <div class="text-center py-10">
          <p class="text-slate-600">No hay unidades de medida registradas.</p>
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
