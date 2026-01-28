@extends('layouts.app')
@section('title', 'Gestión de Proveedores')

@section('header-actions')
  <a href="{{ route('admin.inv.suppliers.create') }}" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Nuevo Proveedor
  </a>
@endsection

@section('content')
  <div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
          Gestión de Proveedores
        </h1>
        <p class="text-sm text-slate-600 mt-1">Administre los proveedores de productos y materiales.</p>
      </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-8">
      <form method="get" id="filtersForm" class="flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-1 space-y-2 w-full">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Buscar Proveedores
          </label>
          <input 
            type="text" 
            name="q" 
            value="{{ $q ?? '' }}" 
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            placeholder="Nombre, email o teléfono..."
          >
        </div>
        
        <div class="w-full md:w-48 space-y-2">
            <label class="block text-sm font-medium text-slate-700">Estado</label>
            <select name="state" onchange="this.form.submit()"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                <option value="all" @selected(($state ?? 'all') === 'all')>Todos</option>
                <option value="active" @selected(($state ?? 'all') === 'active')>Activos</option>
                <option value="inactive" @selected(($state ?? 'all') === 'inactive')>Inactivos</option>
            </select>
        </div>

        <div class="flex gap-2">
          @if(($q ?? '') !== '' || ($state ?? 'all') !== 'all')
            <a href="{{ route('admin.inv.suppliers.index') }}" class="btn btn-ghost flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Limpiar
            </a>
          @endif
        </div>
      </form>
    </div>

    {{-- Estadísticas rápidas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <a href="{{ route('admin.inv.suppliers.index') }}" class="card bg-blue-50 border-blue-200 hover:shadow-md transition-shadow cursor-pointer block text-decoration-none">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-blue-100 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-blue-800">Total Proveedores</p>
            <p class="text-2xl font-bold text-blue-900">{{ $suppliers->total() }}</p>
          </div>
        </div>
      </a>

      <a href="{{ route('admin.inv.suppliers.index', ['state' => 'active']) }}" class="card bg-emerald-50 border-emerald-200 hover:shadow-md transition-shadow cursor-pointer block text-decoration-none">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-emerald-100 rounded-lg">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-emerald-800">Activos</p>
            <p class="text-2xl font-bold text-emerald-900">{{ $activeCount ?? 0 }}</p>
          </div>
        </div>
      </a>

      <a href="{{ route('admin.inv.suppliers.index', ['state' => 'inactive']) }}" class="card bg-slate-50 border-slate-200 hover:shadow-md transition-shadow cursor-pointer block text-decoration-none">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-slate-100 rounded-lg">
            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-slate-800">Inactivos</p>
            <p class="text-2xl font-bold text-slate-900">{{ $inactiveCount ?? 0 }}</p>
          </div>
        </div>
      </a>
    </div>

    {{-- Tabla de proveedores --}}
    <div class="card p-0 overflow-hidden">
      @if($suppliers->count() > 0)
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr class="text-left">
                <th class="px-4 py-3 font-semibold text-slate-700">Proveedor</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Contacto</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Teléfono</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Estado</th>
                <th class="px-4 py-3 font-semibold text-slate-700 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              @foreach($suppliers as $supplier)
                <tr class="hover:bg-slate-50 transition-colors {{ !$supplier->active ? 'bg-slate-50' : '' }}">
                  {{-- Nombre --}}
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $supplier->active ? 'bg-blue-100 text-blue-600' : 'bg-slate-200 text-slate-500' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                      </div>
                      <div>
                        <p class="font-medium {{ $supplier->active ? 'text-slate-800' : 'text-slate-500' }}">{{ $supplier->name }}</p>
                        @if($supplier->notes)
                          <p class="text-xs text-slate-500 truncate max-w-xs">{{ Str::limit($supplier->notes, 50) }}</p>
                        @endif
                      </div>
                    </div>
                  </td>

                  {{-- Email --}}
                  <td class="px-4 py-3">
                    @if($supplier->email)
                      <a href="mailto:{{ $supplier->email }}" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ $supplier->email }}
                      </a>
                    @else
                      <span class="text-slate-400">—</span>
                    @endif
                  </td>

                  {{-- Teléfono --}}
                  <td class="px-4 py-3">
                    @if($supplier->phone)
                      <a href="tel:{{ $supplier->phone }}" class="text-slate-700 hover:text-slate-900 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        {{ $supplier->phone }}
                      </a>
                    @else
                      <span class="text-slate-400">—</span>
                    @endif
                  </td>

                  {{-- Estado --}}
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $supplier->active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                      {{ $supplier->active ? 'Activo' : 'Inactivo' }}
                    </span>
                  </td>

                  {{-- Acciones --}}
                  <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                      <a 
                        href="{{ route('admin.inv.suppliers.edit', $supplier) }}" 
                        class="btn btn-ghost flex items-center gap-1 text-xs px-2 py-1"
                        title="Editar proveedor"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar
                      </a>
                      
                      {{-- Botón toggle en vez de eliminar --}}
                      <form method="post" action="{{ route('admin.inv.suppliers.toggle', $supplier) }}">
                          @csrf
                          <button class="btn text-xs px-2 py-1 flex items-center gap-1 border border-transparent hover:border-slate-300 rounded transition-colors
                                       {{ $supplier->active ? 'text-red-600 hover:bg-red-50' : 'text-emerald-600 hover:bg-emerald-50' }}">
                              @if($supplier->active)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                Desactivar
                              @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
          <h3 class="text-lg font-medium text-slate-700 mb-2">No se encontraron proveedores</h3>
          <p class="text-slate-500 mb-6">"{{ $q }}" no coincide con ningún proveedor.</p>
          <a 
            href="{{ route('admin.inv.suppliers.create') }}" 
            class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 inline-flex"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Proveedor
          </a>
        </div>
      @endif
    </div>

    {{-- Paginación --}}
    @if($suppliers->hasPages())
      <div class="mt-6">
        {{ $suppliers->links() }}
      </div>
    @endif
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.querySelector('input[name="q"]');
        let t;
        if(input) {
            if(input.value.trim() !== '') {
                input.focus();
                const v = input.value;
                input.value = '';
                input.value = v;
            }
            input.addEventListener('input', function() {
                clearTimeout(t);
                t = setTimeout(() => {
                    document.getElementById('filtersForm').submit();
                }, 500);
            });
        }
    });
  </script>
@endsection