{{-- form-fields --}}
@php($category = $category ?? null)

<div class="space-y-4">
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Nombre de la categoría <span class="text-red-500">*</span>
    </label>
    <input
      type="text"
      name="name"
      value="{{ old('name', $category->name ?? '') }}"
      required
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ej: Analgésico, Antibiótico, Anestésico local, Material restaurador"
    >
  </div>

  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Código (opc.)
    </label>
    <input
      type="text"
      name="code"
      value="{{ old('code', $category->code ?? '') }}"
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ej: ANLG, ATB, ANES, MAT-REST"
    >
  </div>

  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Descripción
    </label>
    <textarea
      name="description"
      rows="3"
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Uso general, notas o ejemplos de productos de esta categoría..."
    >{{ old('description', $category->description ?? '') }}</textarea>
  </div>

  <div class="space-y-2">
    <label class="inline-flex items-center gap-3 cursor-pointer">
      <input
        type="checkbox"
        name="is_active"
        value="1"
        @checked(old('is_active', $category->is_active ?? true))
        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
      >
      <span class="text-sm text-slate-700">Categoría activa</span>
    </label>
  </div>
</div>
