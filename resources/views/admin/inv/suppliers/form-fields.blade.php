@php($supplier = $supplier ?? null)

{{-- Nombre --}}
<div class="space-y-2">
  <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
    </svg>
    Nombre del Proveedor <span class="text-red-500">*</span>
  </label>
  <input 
    type="text" 
    name="name" 
    value="{{ old('name', $supplier->name ?? '') }}" 
    class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    placeholder="Nombre completo o razón social"
    required
  >
  <p class="text-xs text-slate-500">Nombre completo o razón social del proveedor.</p>
</div>

{{-- Email --}}
<div class="space-y-2">
  <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
    </svg>
    Correo Electrónico
  </label>
  <input 
    type="email" 
    name="email" 
    value="{{ old('email', $supplier->email ?? '') }}" 
    class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    placeholder="proveedor@ejemplo.com"
  >
  <p class="text-xs text-slate-500">Correo electrónico de contacto (opcional).</p>
</div>

{{-- Teléfono --}}
<div class="space-y-2">
  <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
    </svg>
    Teléfono
  </label>
  <input 
    type="text" 
    name="phone" 
    value="{{ old('phone', $supplier->phone ?? '') }}" 
    class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    placeholder="Número de teléfono"
  >
  <p class="text-xs text-slate-500">Número de teléfono de contacto (opcional).</p>
</div>

{{-- Notas --}}
<div class="md:col-span-2 space-y-2">
  <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    Notas Adicionales
  </label>
  <textarea 
    name="notes" 
    rows="4"
    class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors resize-none"
    placeholder="Información adicional sobre el proveedor..."
  >{{ old('notes', $supplier->notes ?? '') }}</textarea>
  <p class="text-xs text-slate-500">Información adicional como dirección, productos que suministra, etc.</p>
</div>

{{-- Estado --}}
<div class="md:col-span-2 space-y-2">
  <label class="inline-flex items-center gap-3 cursor-pointer">
    <input 
      type="checkbox" 
      name="active" 
      value="1" 
      @checked(old('active', $supplier->active ?? true))
      class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
    >
    <span class="text-sm font-medium text-slate-700">Proveedor Activo</span>
  </label>
  <p class="text-xs text-slate-500">Los proveedores inactivos no estarán disponibles para nuevas compras.</p>
</div>