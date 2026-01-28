@php($product = $product ?? null)

<div class="space-y-6">

    {{-- SECCIÓN 1: IDENTIFICACIÓN BÁSICA --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
        <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            Información General
        </h3>
        
        <div class="grid gap-6 md:grid-cols-2">
            {{-- Nombre --}}
            <div class="md:col-span-2 space-y-2">
                <label class="block text-sm font-medium text-slate-700">Nombre del Producto <span class="text-red-500">*</span></label>
                <input 
                  type="text" 
                  name="name" 
                  value="{{ old('name', $product->name ?? '') }}" 
                  class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-medium text-slate-800 placeholder:text-slate-400"
                  placeholder="Ej: Lidocaína 2% con epinefrina"
                  required
                >
            </div>

            {{-- Marca --}}
            <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-700">Marca / Laboratorio</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </span>
                    <input 
                      type="text" 
                      name="brand" 
                      value="{{ old('brand', $product->brand ?? '') }}" 
                      class="w-full border border-slate-300 rounded-xl pl-10 pr-4 py-3 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"
                      placeholder="Ej: 3M, Dentsply"
                    >
                </div>
            </div>

            {{-- SKU y Barcode Group --}}
            <div class="grid grid-cols-2 gap-4">
                 <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700">SKU (Interno)</label>
                    <input 
                      type="text" 
                      name="sku" 
                      value="{{ old('sku', $product->sku ?? '') }}" 
                      class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-sm"
                      placeholder="Opcional"
                    >
                 </div>
                 <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700">Código Barras</label>
                    <div class="relative">
                        <input 
                          type="text" 
                          name="barcode" 
                          value="{{ old('barcode', $product->barcode ?? '') }}" 
                          class="w-full border border-slate-300 rounded-xl pl-8 pr-3 py-3 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-sm"
                          placeholder="EAN-13"
                        >
                        <span class="absolute left-2.5 top-3.5 text-slate-400">
                             <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </span>
                    </div>
                 </div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN 2: CLASIFICACIÓN Y ORIGEN --}}
    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-visible z-20"> {{-- Z-Index elevated for dropdowns --}}
             <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
             <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                Clasificación
            </h3>
            
            <div class="space-y-5">
                {{-- Categoría --}}
                <x-form.search-select 
                    name="product_category_id" 
                    label="Categoría" 
                    title="Seleccionar Categoría"
                    :options="$categoriesJson ?? []"
                    :value="old('product_category_id', $product->product_category_id ?? '')"
                    :text="$product?->category?->name ?? 'Seleccionar categoría...'">
                </x-form.search-select>

                {{-- Proveedor --}}
                <div>
                     <x-form.search-select 
                        name="supplier_id" 
                        label="Proveedor Principal" 
                        title="Seleccionar Proveedor"
                        :options="$suppliersJson ?? []"
                        :value="old('supplier_id', $product->supplier_id ?? '')"
                        :text="$product?->supplier?->name ?? 'Seleccionar proveedor...'">
                    </x-form.search-select>
                    <p class="text-xs text-slate-500 mt-1.5 ml-1">Proveedor por defecto para órdenes de compra.</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-visible z-10">
            <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
            <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                Presentación
            </h3>

            <div class="space-y-4">
                 {{-- Unidad de Presentación --}}
                 <x-form.search-select 
                    name="presentation_unit_id" 
                    label="Tipo de Empaque" 
                    title="Seleccionar Presentación"
                    :options="$presentationUnitsJson ?? []"
                    :value="old('presentation_unit_id', $product->presentation_unit_id ?? '')"
                    :text="$product?->presentationUnit?->name ?? 'Seleccionar...'">
                </x-form.search-select>

                {{-- Detalle --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700">Contenido / Detalle</label>
                    <input 
                      type="text" 
                      name="presentation_detail" 
                      value="{{ old('presentation_detail', $product->presentation_detail ?? '') }}" 
                      class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-sm"
                      placeholder="Ej: Caja x 100u, Frasco 500ml"
                    >
                </div>

                {{-- Concentración Group --}}
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Concentración (Opcional)</label>
                    <div class="grid grid-cols-2 gap-3">
                         <input 
                            type="number" 
                            name="concentration_value" 
                            step="0.001" 
                            value="{{ old('concentration_value', $product->concentration_value ?? '') }}" 
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            placeholder="Valor (Ej: 500)"
                         >
                         <x-form.search-select 
                            name="concentration_unit_id" 
                            :options="$measurementUnitsJson ?? []"
                            :value="old('concentration_unit_id', $product->concentration_unit_id ?? '')"
                            :text="$product?->concentrationUnit?->name ?? 'Unidad'"
                            title="Unidad">
                        </x-form.search-select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN 3: CONTROL DE INVENTARIO --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-amber-500"></div>
         <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <div class="p-2 bg-amber-50 rounded-lg text-amber-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            Control de Stock
        </h3>

        <div class="grid md:grid-cols-3 gap-6 items-start">
             {{-- Unidad de Manejo --}}
             <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-700">
                    Unidad de Stock <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input 
                      type="text" 
                      name="unit" 
                      value="{{ old('unit', $product->unit ?? 'unidad') }}" 
                      class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"
                      placeholder="Ej: unidad, ml, gr"
                      required
                    >
                    <div class="absolute right-3 top-3.5 text-xs text-slate-400">
                        Cómo se cuenta
                    </div>
                </div>
                <p class="text-xs text-slate-500">Unidad mínima de conteo en inventario.</p>
             </div>

             {{-- Min Stock --}}
             <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-700">Stock Mínimo (Alerta)</label>
                <input 
                  type="number" 
                  name="min_stock" 
                  step="1" 
                  min="0" 
                  value="{{ old('min_stock', $product->min_stock ?? 0) }}" 
                  class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold text-slate-700"
                >
             </div>

             {{-- Toggle Activo --}}
             <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 mt-1">
                 <label class="flex items-center gap-4 cursor-pointer group">
                     <div class="relative">
                         <input type="checkbox" name="is_active" value="1" class="sr-only peer" @checked(old('is_active', $product->is_active ?? true))>
                         <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                     </div>
                     <div>
                         <span class="block text-sm font-medium text-slate-800 group-hover:text-blue-700 transition-colors">Producto Habilitado</span>
                         <span class="block text-xs text-slate-500">Visible en selectores de mov.</span>
                     </div>
                 </label>
             </div>
        </div>
    </div>

</div>
