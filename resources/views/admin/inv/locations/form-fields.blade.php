@php($location = $location ?? null)

<div class="space-y-4">
  {{-- Nombre --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Nombre de la ubicación <span class="text-red-500">*</span>
    </label>
    <input
      type="text"
      name="name"
      value="{{ old('name', $location->name ?? '') }}"
      required
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ej: Depósito principal, Box 1, Gabinete medicamentos"
    >
  </div>

  {{-- Código --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Código interno
    </label>
    <input
      type="text"
      name="code"
      value="{{ old('code', $location->code ?? '') }}"
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ej: DEP-01, BOX-1"
    >
  </div>

  {{-- Principal & activa --}}
  <div class="space-y-3">
    <label class="block text-sm font-medium text-slate-700 mb-1">Opciones</label>

    <label class="inline-flex items-center gap-3 cursor-pointer">
      <input
        type="checkbox"
        name="is_main"
        value="1"
        @checked(old('is_main', $location->is_main ?? false))
        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
      >
      <span class="text-sm text-slate-700">Ubicación principal</span>
    </label>

    <label class="inline-flex items-center gap-3 cursor-pointer">
      <input
        type="checkbox"
        name="active"
        value="1"
        @checked(old('active', $location->active ?? true))
        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
      >
      <span class="text-sm text-slate-700">Ubicación activa</span>
    </label>
  </div>
</div>
