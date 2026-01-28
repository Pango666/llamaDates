@php($supplier = $supplier ?? null)

<div class="space-y-4">
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Nombre del proveedor / laboratorio <span class="text-red-500">*</span>
    </label>
    <input
      type="text"
      name="name"
      value="{{ old('name', $supplier->name ?? '') }}"
      required
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ej: Laboratorios ACME, Droguería Central"
    >
  </div>

  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Correo electrónico
    </label>
    <input
      type="email"
      name="email"
      value="{{ old('email', $supplier->email ?? '') }}"
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="correo@ejemplo.com"
    >
  </div>

  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Persona de contacto
    </label>
    <input
      type="text"
      name="contact"
      value="{{ old('contact', $supplier->contact ?? '') }}"
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Nombre del contacto"
    >
  </div>

  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Teléfono
    </label>
    <input
      type="text"
      name="phone"
      value="{{ old('phone', $supplier->phone ?? '') }}"
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Teléfono o celular"
    >
  </div>

  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      NIT / ID fiscal
    </label>
    <input
      type="text"
      name="tax_id"
      value="{{ old('tax_id', $supplier->tax_id ?? '') }}"
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="NIT, RUC, RFC, etc."
    >
  </div>
</div>
