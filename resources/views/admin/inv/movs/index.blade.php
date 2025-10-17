@extends('layouts.app')
@section('title', 'Movimientos de Inventario')

@section('header-actions')
  <a href="{{ route('admin.inv.movs.create') }}" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Nuevo Movimiento
  </a>
@endsection

@section('content')
  <div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
          </svg>
          Movimientos de Inventario
        </h1>
        <p class="text-sm text-slate-600 mt-1">Registro de entradas y salidas de productos del inventario.</p>
      </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-6">
      <form method="get" class="grid gap-4 md:grid-cols-6">
        {{-- Producto --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Producto
          </label>
          <select 
            name="product_id" 
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
            <option value="0">— Todos los productos —</option>
            @foreach($products as $product)
              <option value="{{ $product->id }}" @selected($qProd == $product->id)>
                {{ $product->name }} @if($product->sku) ({{ $product->sku }}) @endif
              </option>
            @endforeach
          </select>
        </div>

        {{-- Ubicación --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Ubicación
          </label>
          <select 
            name="location_id" 
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
            <option value="0">— Todas las ubicaciones —</option>
            @foreach($locations as $location)
              <option value="{{ $location->id }}" @selected($qLoc == $location->id)>
                {{ $location->name }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Tipo --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            Tipo
          </label>
          <select 
            name="type" 
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
            <option value="all" @selected($type === 'all')>Todos los tipos</option>
            <option value="in" @selected($type === 'in')>Entrada</option>
            <option value="out" @selected($type === 'out')>Salida</option>
          </select>
        </div>

        {{-- Fecha Desde --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Desde
          </label>
          <input 
            type="date" 
            name="from" 
            value="{{ $from }}" 
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
        </div>

        {{-- Fecha Hasta --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Hasta
          </label>
          <input 
            type="date" 
            name="to" 
            value="{{ $to }}" 
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
        </div>

        {{-- Botones --}}
        <div class="md:col-span-6 flex gap-2 pt-2">
          <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Aplicar Filtros
          </button>
          @if($qProd != 0 || $qLoc != 0 || $type != 'all' || $from || $to)
            <a href="{{ route('admin.inv.movs.index') }}" class="btn btn-ghost flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Limpiar Filtros
            </a>
          @endif
        </div>
      </form>
    </div>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="card bg-blue-50 border-blue-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-blue-100 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-blue-800">Entradas</p>
            <p class="text-2xl font-bold text-blue-900">
              {{ $movs->where('type', 'in')->count() }}
            </p>
          </div>
        </div>
      </div>

      <div class="card bg-rose-50 border-rose-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-rose-100 rounded-lg">
            <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-rose-800">Salidas</p>
            <p class="text-2xl font-bold text-rose-900">
              {{ $movs->where('type', 'out')->count() }}
            </p>
          </div>
        </div>
      </div>

      <div class="card bg-slate-50 border-slate-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-slate-100 rounded-lg">
            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-slate-800">Total</p>
            <p class="text-2xl font-bold text-slate-900">{{ $movs->total() }}</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Tabla de movimientos --}}
    <div class="card p-0 overflow-hidden">
      @if($movs->count() > 0)
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr class="text-left">
                <th class="px-4 py-3 font-semibold text-slate-700">Fecha y Hora</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Producto</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Ubicación</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Tipo</th>
                <th class="px-4 py-3 font-semibold text-slate-700 text-right">Cantidad</th>
                <th class="px-4 py-3 font-semibold text-slate-700 text-right">Costo Unit.</th>
                <th class="px-4 py-3 font-semibold text-slate-700 text-right">Total</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Notas</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              @foreach($movs as $movement)
                <tr class="hover:bg-slate-50 transition-colors">
                  {{-- Fecha --}}
                  <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                    {{ $movement->created_at->format('d/m/Y H:i') }}
                  </td>

                  {{-- Producto --}}
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                      </div>
                      <div>
                        <p class="font-medium text-slate-800">{{ $movement->product->name }}</p>
                        @if($movement->product->sku)
                          <p class="text-xs text-slate-500">{{ $movement->product->sku }}</p>
                        @endif
                      </div>
                    </div>
                  </td>

                  {{-- Ubicación --}}
                  <td class="px-4 py-3 text-slate-600">
                    {{ $movement->location->name }}
                  </td>

                  {{-- Tipo --}}
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $movement->type === 'in' ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800' }}">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($movement->type === 'in')
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        @else
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        @endif
                      </svg>
                      {{ $movement->type === 'in' ? 'Entrada' : 'Salida' }}
                    </span>
                  </td>

                  {{-- Cantidad --}}
                  <td class="px-4 py-3 text-right font-medium {{ $movement->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $movement->type === 'in' ? '+' : '-' }}{{ number_format($movement->qty, 4) }}
                    <span class="text-xs text-slate-500 block">{{ $movement->product->unit }}</span>
                  </td>

                  {{-- Costo Unitario --}}
                  <td class="px-4 py-3 text-right text-slate-600">
                    Bs {{ number_format($movement->unit_cost_at_issue, 4) }}
                  </td>

                  {{-- Total --}}
                  <td class="px-4 py-3 text-right font-semibold {{ $movement->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                    Bs {{ number_format($movement->qty * $movement->unit_cost_at_issue, 2) }}
                  </td>

                  {{-- Notas --}}
                  <td class="px-4 py-3 text-slate-600 max-w-xs">
                    @if($movement->notes)
                      <div class="truncate" title="{{ $movement->notes }}">
                        {{ $movement->notes }}
                      </div>
                    @else
                      <span class="text-slate-400">—</span>
                    @endif
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
          </svg>
          <h3 class="text-lg font-medium text-slate-700 mb-2">No hay movimientos registrados</h3>
          <p class="text-slate-500 mb-6">No se encontraron movimientos con los filtros aplicados.</p>
          <a 
            href="{{ route('admin.inv.movs.create') }}" 
            class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 inline-flex"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Registrar Primer Movimiento
          </a>
        </div>
      @endif
    </div>

    {{-- Paginación --}}
    @if($movs->hasPages())
      <div class="mt-6">
        {{ $movs->links() }}
      </div>
    @endif
  </div>
@endsection