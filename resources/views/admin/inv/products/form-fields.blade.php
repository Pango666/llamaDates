@php($product = $product ?? null)

<div class="grid gap-6 md:grid-cols-3 mb-6">
  {{-- SKU --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
      </svg>
      SKU (Código interno)
    </label>
    <input 
      type="text" 
      name="sku" 
      value="{{ old('sku', $product->sku ?? '') }}" 
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Código interno del producto"
    >
    <p class="text-xs text-slate-500">Identificador interno opcional (no visible al paciente).</p>
  </div>

  {{-- Código de barras --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h2m2 0h2m2 0h2m2 0h2M5 19h2m2 0h2m2 0h2m2 0h2M7 5v14m4-14v14m4-14v14m4-14v14"/>
      </svg>
      Código de barras
    </label>
    <input 
      type="text" 
      name="barcode" 
      value="{{ old('barcode', $product->barcode ?? '') }}" 
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="EAN / código de barras del producto"
    >
    <p class="text-xs text-slate-500">Puede usar el lector para llenar este campo.</p>
  </div>

  {{-- Estado --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">Estado</label>
    <label class="inline-flex items-center gap-3 cursor-pointer mt-1">
      <input 
        type="checkbox" 
        name="is_active" 
        value="1" 
        @checked(old('is_active', $product->is_active ?? true))
        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
      >
      <span class="text-sm text-slate-700">Producto activo</span>
    </label>
    <p class="text-xs text-slate-500">Los productos inactivos no aparecerán para selección en insumos.</p>
  </div>
</div>

<div class="grid gap-6 md:grid-cols-3 mb-6">
  {{-- Nombre --}}
  <div class="space-y-2 md:col-span-2">
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
      placeholder="Ej: Lidocaína 2% carpule"
      required
    >
  </div>

  {{-- Marca / laboratorio --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/>
      </svg>
      Marca / Laboratorio
    </label>
    <input 
      type="text" 
      name="brand" 
      value="{{ old('brand', $product->brand ?? '') }}" 
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Nombre comercial o laboratorio"
    >
  </div>
</div>

<div class="grid gap-6 md:grid-cols-3 mb-6">
  {{-- Categoría --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h10M4 14h8M4 18h6"/>
      </svg>
      Categoría
    </label>
    <select
      name="product_category_id"
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    >
      <option value="">Sin categoría</option>
      @foreach($categories as $cat)
        <option value="{{ $cat->id }}" @selected(old('product_category_id', $product->product_category_id ?? null) == $cat->id)>
          {{ $cat->name }}
        </option>
      @endforeach
    </select>
    <p class="text-xs text-slate-500">Ej: Analgésico, Antibiótico, Anestésico local, Material odontológico, etc.</p>
  </div>

  {{-- Unidad de presentación --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
      </svg>
      Presentación
    </label>
    <select
      name="presentation_unit_id"
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    >
      <option value="">Sin especificar</option>
      @foreach($presentationUnits as $unit)
        <option value="{{ $unit->id }}" @selected(old('presentation_unit_id', $product->presentation_unit_id ?? null) == $unit->id)>
          {{ $unit->name }}
        </option>
      @endforeach
    </select>
    <p class="text-xs text-slate-500">Ej: Ampolla, Tableta, Cápsula, Frasco, Carpule, Caja, etc.</p>
  </div>

  {{-- Detalle de presentación --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Detalle de presentación
    </label>
    <input 
      type="text" 
      name="presentation_detail" 
      value="{{ old('presentation_detail', $product->presentation_detail ?? '') }}" 
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ej: Caja x 50 carpules, Frasco 250 ml"
    >
  </div>
</div>

<div class="grid gap-6 md:grid-cols-3 mb-6">
  {{-- Concentración valor --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Concentración (valor)
    </label>
    <input 
      type="number" 
      name="concentration_value" 
      step="0.001" 
      min="0" 
      value="{{ old('concentration_value', $product->concentration_value ?? '') }}" 
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ej: 2, 400, 500, 0.12"
    >
    <p class="text-xs text-slate-500">Ejemplos: 2 (%), 400 (mg), 500 (mg), 0.12 (%).</p>
  </div>

  {{-- Concentración unidad --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Unidad de concentración
    </label>
    <select
      name="concentration_unit_id"
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    >
      <option value="">Sin unidad</option>
      @foreach($measurementUnits as $mu)
        <option value="{{ $mu->id }}" @selected(old('concentration_unit_id', $product->concentration_unit_id ?? null) == $mu->id)>
          {{ $mu->symbol }} — {{ $mu->name }}
        </option>
      @endforeach
    </select>
    <p class="text-xs text-slate-500">Ej: mg, ml, %, mg/ml, UI.</p>
  </div>

  {{-- Unidad interna (para stock) --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Unidad (para stock) <span class="text-red-500">*</span>
    </label>
    <input 
      type="text" 
      name="unit" 
      value="{{ old('unit', $product->unit ?? 'unidad') }}" 
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ej: carpule, tableta, cápsula, frasco, caja"
      required
    >
    <p class="text-xs text-slate-500">Cómo se cuenta en stock: carpule, tableta, cápsula, frasco, caja, etc.</p>
  </div>
</div>

<div class="grid gap-6 md:grid-cols-3 mb-6">
  {{-- Proveedor / laboratorio --}}
  <div class="space-y-2 md:col-span-2">
    <label class="block text-sm font-medium text-slate-700">
      Proveedor / Laboratorio
    </label>
    <select
      name="supplier_id"
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    >
      <option value="">Sin proveedor</option>
      @foreach($suppliers as $sup)
        <option value="{{ $sup->id }}" @selected(old('supplier_id', $product->supplier_id ?? null) == $sup->id)>
          {{ $sup->name }}
        </option>
      @endforeach
    </select>
    <p class="text-xs text-slate-500">Define el proveedor principal para este producto.</p>
  </div>

  {{-- Stock mínimo --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      Stock mínimo
    </label>
    <input 
      type="number" 
      name="min_stock" 
      step="1" 
      min="0" 
      value="{{ old('min_stock', $product->min_stock ?? 0) }}" 
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    >
    <p class="text-xs text-slate-500">Se usará para alertas de stock bajo.</p>
  </div>
</div>
