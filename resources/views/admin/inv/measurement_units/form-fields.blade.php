@php($measurementUnit = $measurementUnit ?? null)

<div class="space-y-4">
  {{-- Nombre --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Nombre de la unidad <span class="text-red-500">*</span>
    </label>
    <input
      type="text"
      name="name"
      value="{{ old('name', $measurementUnit->name ?? '') }}"
      required
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ej: Miligramos, Mililitros, Porcentaje"
    >
  </div>

  {{-- Símbolo --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Símbolo <span class="text-red-500">*</span>
    </label>
    <input
      type="text"
      name="symbol"
      value="{{ old('symbol', $measurementUnit->symbol ?? '') }}"
      required
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ej: mg, ml, %, UI"
    >
  </div>

  {{-- Tipo --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700">
      Tipo
    </label>
    <select
      name="type"
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    >
      @php($type = old('type', $measurementUnit->type ?? ''))
      <option value="">Sin tipo</option>
      <option value="mass"   @selected($type === 'mass')>Masa (mg, g, kg)</option>
      <option value="volume" @selected($type === 'volume')>Volumen (ml, L)</option>
      <option value="ratio"  @selected($type === 'ratio')>Relación / %</option>
      <option value="other"  @selected($type === 'other')>Otra</option>
    </select>
  </div>

  {{-- Activo --}}
  <div class="space-y-2">
    <label class="inline-flex items-center gap-3 cursor-pointer">
      <input
        type="checkbox"
        name="is_active"
        value="1"
        @checked(old('is_active', $measurementUnit->is_active ?? true))
        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
      >
      <span class="text-sm text-slate-700">Unidad activa</span>
    </label>
  </div>
</div>
