@php($product = $product ?? null)

{{-- SKU --}}
<div class="space-y-2">
  <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
    </svg>
    SKU (Código)
  </label>
  <input 
    type="text" 
    name="sku" 
    value="{{ old('sku', $product->sku ?? '') }}" 
    class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    placeholder="Código único del producto"
  >
  <p class="text-xs text-slate-500">Identificador único para el producto (opcional).</p>
</div>

{{-- Nombre --}}
<div class="space-y-2">
  <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
    </svg>
    Nombre del Producto <span class="text-red-500">*</span>
  </label>
  <input 
    type="text" 
    name="name" 
    value="{{ old('name', $product->name ?? '') }}" 
    class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    placeholder="Nombre descriptivo del producto"
    required
  >
</div>

{{-- Unidad --}}
<div class="space-y-2">
  <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
    </svg>
    Unidad de Medida <span class="text-red-500">*</span>
  </label>
  <input 
    type="text" 
    name="unit" 
    value="{{ old('unit', $product->unit ?? 'und') }}" 
    class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    placeholder="Ej: und, kg, ml, etc."
    required
  >
  <p class="text-xs text-slate-500">Unidad de medida para el inventario.</p>
</div>

{{-- Precio Venta --}}
<div class="space-y-2">
  <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
    </svg>
    Precio de Venta (Bs) <span class="text-red-500">*</span>
  </label>
  <input 
    type="number" 
    name="sell_price" 
    step="0.01" 
    min="0" 
    value="{{ old('sell_price', $product->sell_price ?? 0) }}" 
    class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    required
  >
</div>

{{-- Costo Promedio --}}
<div class="space-y-2">
  <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
    </svg>
    Costo Promedio (Bs)
  </label>
  <input 
    type="number" 
    name="cost_avg" 
    step="0.0001" 
    min="0" 
    value="{{ old('cost_avg', $product->cost_avg ?? 0) }}" 
    class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
  >
  <p class="text-xs text-slate-500">Costo promedio de adquisición.</p>
</div>

{{-- Stock Mínimo --}}
<div class="space-y-2">
  <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    Stock Mínimo
  </label>
  <input 
    type="number" 
    name="min_stock" 
    step="0.0001" 
    min="0" 
    value="{{ old('min_stock', $product->min_stock ?? 0) }}" 
    class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
  >
  <p class="text-xs text-slate-500">Stock mínimo para alertas de inventario.</p>
</div>

{{-- Estado --}}
<div class="md:col-span-3 space-y-2">
  <label class="inline-flex items-center gap-3 cursor-pointer">
    <input 
      type="checkbox" 
      name="active" 
      value="1" 
      @checked(old('active', $product->active ?? true))
      class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
    >
    <span class="text-sm font-medium text-slate-700">Producto Activo</span>
  </label>
  <p class="text-xs text-slate-500">Los productos inactivos no estarán disponibles para ventas.</p>
</div>