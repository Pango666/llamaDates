@php($presentationUnit = $presentationUnit ?? null)

<div class="space-y-4">
  {{-- Nombre --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Nombre de la presentación <span class="text-red-500">*</span>
    </label>
    <input
      type="text"
      name="name"
      value="{{ old('name', $presentationUnit->name ?? '') }}"
      required
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ej: Ampolla, Tableta, Cápsula, Frasco, Carpule, Caja"
    >
  </div>

  {{-- Descripción --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Descripción
    </label>
    <textarea
      name="description"
      rows="3"
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Notas adicionales de esta presentación..."
    >{{ old('description', $presentationUnit->description ?? '') }}</textarea>
  </div>

  {{-- Activo --}}
  <div class="space-y-2">
    <label class="inline-flex items-center gap-3 cursor-pointer">
      <input
        type="checkbox"
        name="is_active"
        value="1"
        @checked(old('is_active', $presentationUnit->is_active ?? true))
        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
      >
      <span class="text-sm text-slate-700">Presentación activa</span>
    </label>
  </div>
</div>
