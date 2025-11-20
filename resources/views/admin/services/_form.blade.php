@php $isEdit = $service->exists; @endphp

<style>
  .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:.5rem;font-weight:500;border:1px solid transparent;text-decoration:none}
  .btn-danger{background:#ef4444;color:#fff;border-color:#ef4444}
  .btn-danger:hover{background:#dc2626;border-color:#dc2626}
</style>

<div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
  {{-- Nombre del servicio --}}
  <div class="md:col-span-2 space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
      </svg>
      Nombre del servicio <span class="text-red-500">*</span>
    </label>
    <input
      name="name"
      value="{{ old('name', $service->name) }}"
      required
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="Ej: Limpieza dental, Obturación, Ortodoncia..."
    >
    @error('name')
      <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $message }}
      </p>
    @enderror
  </div>

  {{-- Duración --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      Duración (minutos) <span class="text-red-500">*</span>
    </label>
    <input
      type="number" min="5" max="480" step="5"
      name="duration_min"
      value="{{ old('duration_min', $service->duration_min) }}"
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="30"
    >
    @error('duration_min')
      <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $message }}
      </p>
    @enderror
  </div>

  {{-- Precio --}}
  <div class="space-y-2">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
      <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
      </svg>
      Precio (Bs) <span class="text-red-500">*</span>
    </label>
    <input
      type="number" min="0" step="0.01" name="price"
      value="{{ old('price', $service->price) }}"
      class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      placeholder="0.00"
    >
    @error('price')
      <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $message }}
      </p>
    @enderror
  </div>

  {{-- Estado activo --}}
  <div class="lg:col-span-2 flex items-end">
    <label class="inline-flex items-center gap-3 p-3 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer">
      <input
        type="checkbox" name="active" value="1"
        @checked(old('active', $service->active ?? true))
        class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
      >
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-sm font-medium text-slate-700">Servicio activo</span>
      </div>
    </label>
    <p class="text-xs text-slate-500 ml-3">
      Los servicios inactivos no estarán disponibles para nuevas citas.
    </p>
  </div>
</div>

{{-- Acciones del formulario --}}
<div class="flex gap-3 pt-6 mt-6 border-t border-slate-200">
  <button type="submit"
          class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ $isEdit ? 'Actualizar Servicio' : 'Registrar Servicio' }}
  </button>

  <a href="{{ route('admin.services') }}"
     class="btn btn-danger flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
    Cancelar
  </a>
</div>
