@extends('layouts.app')
@section('title', 'Gestión de Productos')

@section('header-actions')
  <a href="{{ route('admin.inv.products.create') }}" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Nuevo Producto
  </a>
@endsection

@section('content')
  <div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
          Gestión de Productos
        </h1>
        <p class="text-sm text-slate-600 mt-1">Administre medicamentos, insumos y materiales del consultorio.</p>
      </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-6">
      <form method="get" class="grid gap-4 md:grid-cols-4 md:items-end">
        {{-- Búsqueda --}}
        <div class="space-y-2 md:col-span-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Buscar
          </label>
          <input 
            type="text" 
            name="q" 
            value="{{ $q }}" 
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            placeholder="Nombre, SKU, código de barras, marca..."
          >
        </div>

        {{-- Estado --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Estado
          </label>
          <select 
            name="active" 
            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
            <option value="all" @selected($active == 'all')>Todos</option>
            <option value="1" @selected($active == '1')>Activos</option>
            <option value="0" @selected($active == '0')>Inactivos</option>
          </select>
        </div>

        {{-- Botones --}}
        <div class="md:col-span-1 flex gap-2">
          <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Filtrar
          </button>
          @if($q !== '' || $active !== 'all')
            <a href="{{ route('admin.inv.products.index') }}" class="btn btn-ghost flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Limpiar
            </a>
          @endif
        </div>
      </form>
    </div>

    @php
      $pageProducts   = $products->getCollection();
      $activeCount    = $pageProducts->where('is_active', 1)->count();
      $inactiveCount  = $pageProducts->where('is_active', 0)->count();
      $lowStockCount  = $pageProducts->filter(function ($p) use ($stockMap) {
        $stock = (float)($stockMap[$p->id] ?? 0);
        $min   = (float)($p->min_stock ?? 0);
        return $min > 0 && $stock <= $min;
      })->count();
    @endphp

    {{-- Estadísticas rápidas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="card bg-blue-50 border-blue-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-blue-100 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-blue-800">Total Productos</p>
            <p class="text-2xl font-bold text-blue-900">{{ $products->total() }}</p>
          </div>
        </div>
      </div>

      <div class="card bg-emerald-50 border-emerald-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-emerald-100 rounded-lg">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-emerald-800">Activos (en página)</p>
            <p class="text-2xl font-bold text-emerald-900">{{ $activeCount }}</p>
          </div>
        </div>
      </div>

      <div class="card bg-amber-50 border-amber-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-amber-100 rounded-lg">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-amber-800">Stock Bajo (página)</p>
            <p class="text-2xl font-bold text-amber-900">{{ $lowStockCountPage }}</p>
          </div>
        </div>
      </div>

      <div class="card bg-orange-50 border-orange-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-orange-100 rounded-lg">
            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-orange-800">Por Vencer</p>
            <p class="text-2xl font-bold text-orange-900">{{ $expiringSoonCount }}</p>
          </div>
        </div>
      </div>

      <div class="card bg-rose-50 border-rose-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-rose-100 rounded-lg">
            <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-rose-800">Vencidos</p>
            <p class="text-2xl font-bold text-rose-900">{{ $expiredCount }}</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Tabla de productos --}}
    <div class="card p-0 overflow-hidden">
      @if($products->count() > 0)
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr class="text-left">
                <th class="px-4 py-3 font-semibold text-slate-700">SKU</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Código barras</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Producto</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Categoría</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Presentación</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Vencimiento (Próx)</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Proveedor</th>
                <th class="px-4 py-3 font-semibold text-slate-700 text-right">Stock</th>
                <th class="px-4 py-3 font-semibold text-slate-700">Estado</th>
                <th class="px-4 py-3 font-semibold text-slate-700 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              @foreach($products as $product)
                @php
                  $currentStock = (float)($stockMap[$product->id] ?? 0);
                  $minStock     = (float)($product->min_stock ?? 0);
                  $isLowStock   = $minStock > 0 && $currentStock <= $minStock;
                  $isActive     = (bool)($product->is_active ?? false);
                @endphp
                <tr class="hover:bg-slate-50 transition-colors {{ $isLowStock ? 'bg-amber-50' : '' }}">
                  {{-- SKU --}}
                  <td class="px-4 py-3">
                    @if($product->sku)
                      <code class="text-xs bg-slate-100 px-2 py-1 rounded text-slate-700">{{ $product->sku }}</code>
                    @else
                      <span class="text-slate-400">—</span>
                    @endif
                  </td>

                  {{-- Código barras --}}
                  <td class="px-4 py-3">
                    @if($product->barcode)
                      <code class="text-xs bg-slate-100 px-2 py-1 rounded text-slate-700">{{ $product->barcode }}</code>
                    @else
                      <span class="text-slate-400">—</span>
                    @endif
                  </td>

                  {{-- Nombre / marca / concentración --}}
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                      </div>
                      <div>
                        <p class="font-medium text-slate-800">{{ $product->name }}</p>
                        <div class="text-xs text-slate-500 space-x-2">
                          @if($product->brand)
                            <span>{{ $product->brand }}</span>
                          @endif
                          @if($product->concentration_label)
                            <span>· {{ $product->concentration_label }}</span>
                          @endif
                        </div>

                        @if($isLowStock)
                          <p class="text-xs text-amber-600 flex items-center gap-1 mt-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            Stock bajo
                          </p>
                        @endif
                      </div>
                    </div>
                  </td>

                  {{-- Categoría --}}
                  <td class="px-4 py-3 text-slate-600">
                    {{ $product->category?->name ?? '—' }}
                  </td>

                  <td class="px-4 py-3 text-slate-600">
                    {{ $product->presentation_label ?? '—' }}
                  </td>
                  
                  {{-- Vencimiento --}}
                  <td class="px-4 py-3">
                    @if(isset($nearestExpirationMap[$product->id]))
                      @php
                          $expDate = \Carbon\Carbon::parse($nearestExpirationMap[$product->id]);
                          $isExpiring = $expDate->isPast();
                          $isSoon = !$isExpiring && $expDate->diffInDays(now()) <= 30;
                      @endphp
                      <span class="text-xs font-medium px-2 py-1 rounded {{ $isExpiring ? 'bg-rose-100 text-rose-800' : ($isSoon ? 'bg-orange-100 text-orange-800' : 'bg-emerald-100 text-emerald-800') }}">
                        {{ $expDate->format('d/m/Y') }}
                      </span>
                    @else
                      <span class="text-slate-400 text-xs">—</span>
                    @endif
                  </td>

                  {{-- Proveedor --}}
                  <td class="px-4 py-3 text-slate-600">
                    {{ $product->supplier?->name ?? '—' }}
                  </td>

                  {{-- Stock --}}
                  <td class="px-4 py-3 text-right">
                    <span class="font-medium {{ $isLowStock ? 'text-amber-600' : 'text-slate-800' }}">
                      {{ number_format($currentStock, 2) }} {{ $product->unit }}
                    </span>
                    @if($minStock > 0)
                      <div class="text-xs text-slate-500">Mín: {{ number_format($minStock, 2) }}</div>
                    @endif
                  </td>

                  {{-- Estado --}}
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $isActive ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($isActive)
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        @else
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        @endif
                      </svg>
                      {{ $isActive ? 'Activo' : 'Inactivo' }}
                    </span>
                  </td>

                  {{-- Acciones --}}
                  <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                      <a 
                        href="{{ route('admin.inv.products.edit', $product) }}" 
                        class="btn btn-ghost flex items-center gap-1"
                        title="Editar producto"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar
                      </a>
                      <form 
                        method="post" 
                        action="{{ route('admin.inv.products.destroy', $product) }}" 
                        class="inline"
                        onsubmit="return confirm('¿Está seguro de eliminar este producto? Esta acción no se puede deshacer.')"
                      >
                        @csrf @method('DELETE')
                        <button 
                          class="btn bg-red-600 text-white hover:bg-red-700 flex items-center gap-1"
                          title="Eliminar producto"
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
          <h3 class="text-lg font-medium text-slate-700 mb-2">No hay productos registrados</h3>
          <p class="text-slate-500 mb-6">Comience agregando el primer producto al inventario.</p>
          <a 
            href="{{ route('admin.inv.products.create') }}" 
            class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 inline-flex"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Agregar Primer Producto
          </a>
        </div>
      @endif
    </div>

    {{-- Paginación --}}
    @if($products->hasPages())
      <div class="mt-6">
        {{ $products->links() }}
      </div>
    @endif
  </div>
@endsection
