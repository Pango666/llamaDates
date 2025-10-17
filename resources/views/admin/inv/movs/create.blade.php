@extends('layouts.app')
@section('title', 'Nuevo Movimiento de Inventario')

@section('header-actions')
  <a href="{{ route('admin.inv.movs.index') }}" class="btn btn-ghost flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Movimientos
  </a>
@endsection

@section('content')
  <div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nuevo Movimiento
        </h1>
        <p class="text-sm text-slate-600 mt-1">Registre una nueva entrada o salida de inventario.</p>
      </div>
    </div>

    <form method="post" action="{{ route('admin.inv.movs.store') }}" class="card">
      @csrf

      <div class="grid gap-6 md:grid-cols-3">
        {{-- Producto --}}
        <div class="md:col-span-2 space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Producto <span class="text-red-500">*</span>
          </label>
          <select 
            name="product_id" 
            class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            required
          >
            @foreach($products as $product)
              <option value="{{ $product->id }}">
                {{ $product->name }} 
                @if($product->sku) ({{ $product->sku }}) @endif
                · Stock: {{ number_format($product->current_stock ?? 0, 2) }} {{ $product->unit }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Ubicación --}}
        {{-- <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Ubicación <span class="text-red-500">*</span>
          </label>
          <select 
            name="location_id" 
            class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            required
          >
            @foreach($locations as $location)
              <option value="{{ $location->id }}">{{ $location->name }}</option>
            @endforeach
          </select>
        </div> --}}

        {{-- Tipo --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            Tipo de Movimiento <span class="text-red-500">*</span>
          </label>
          <select 
            name="type" 
            id="typeSel"
            class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            required
          >
            <option value="in">Entrada (Ingreso)</option>
            <option value="out">Salida (Egreso)</option>
          </select>
        </div>

        {{-- Cantidad --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            Cantidad <span class="text-red-500">*</span>
          </label>
          <input 
            type="number" 
            name="qty" 
            step="0.0001" 
            min="0.0001" 
            value="1" 
            class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            required
          >
        </div>

        {{-- Costo Unitario --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
            </svg>
            Costo Unitario (Bs)
          </label>
          <input 
            type="number" 
            name="unit_cost" 
            step="0.0001" 
            min="0" 
            id="costInput"
            class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            placeholder="Costo unitario del producto..."
          >
          <div id="costHelp" class="text-xs text-blue-600">
            💡 Para entradas: ingrese el costo de compra. Para salidas: vacío = costo promedio actual.
          </div>
        </div>

        {{-- Notas --}}
        <div class="md:col-span-3 space-y-2">
          <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Notas (Opcional)
          </label>
          <input 
            name="notes" 
            class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            placeholder="Motivo del movimiento, proveedor, etc."
          >
        </div>
      </div>

      {{-- Resumen --}}
      <div class="mt-6 p-4 bg-slate-50 border border-slate-200 rounded-lg">
        <h4 class="font-medium text-slate-800 mb-2 flex items-center gap-2">
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Resumen del Movimiento
        </h4>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <span class="text-slate-600">Tipo:</span>
            <span id="summaryType" class="font-medium ml-2">Entrada</span>
          </div>
          <div>
            <span class="text-slate-600">Costo aplicado:</span>
            <span id="summaryCost" class="font-medium ml-2">Bs 0.0000</span>
          </div>
        </div>
      </div>

      {{-- Acciones --}}
      <div class="flex items-center gap-4 pt-6 mt-6 border-t border-slate-200">
        <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Registrar Movimiento
        </button>
        <a href="{{ route('admin.inv.movs.index') }}" class="btn btn-ghost flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Cancelar
        </a>
      </div>
    </form>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const typeSel = document.getElementById('typeSel');
      const costInput = document.getElementById('costInput');
      const costHelp = document.getElementById('costHelp');
      const productSelect = document.querySelector('select[name="product_id"]');
      
      const summaryType = document.getElementById('summaryType');
      const summaryCost = document.getElementById('summaryCost');

      function updateCostPlaceholder() {
        if (typeSel.value === 'out') {
          costInput.placeholder = 'Vacío = usa costo promedio actual';
          costHelp.textContent = '💡 Para salidas: dejar vacío para usar el costo promedio actual del producto.';
          costHelp.className = 'text-xs text-amber-600';
        } else {
          costInput.placeholder = 'Requerido para entrada (costo de compra)';
          costHelp.textContent = '💡 Para entradas: ingrese el costo de compra para actualizar el costo promedio.';
          costHelp.className = 'text-xs text-blue-600';
        }
        updateSummary();
      }

      function updateSummary() {
        const selectedProduct = productSelect.options[productSelect.selectedIndex];
        const productCost = selectedProduct ? parseFloat(selectedProduct.getAttribute('data-cost')) || 0 : 0;
        const currentCost = costInput.value ? parseFloat(costInput.value) : productCost;
        
        summaryType.textContent = typeSel.value === 'in' ? 'Entrada' : 'Salida';
        summaryCost.textContent = `Bs ${currentCost.toFixed(4)}`;
      }

      typeSel?.addEventListener('change', updateCostPlaceholder);
      costInput?.addEventListener('input', updateSummary);
      productSelect?.addEventListener('change', updateSummary);

      // Inicializar
      updateCostPlaceholder();
      updateSummary();
    });
  </script>
@endsection