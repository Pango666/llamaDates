@php
  $isEdit = $service->exists;

  $discountActive = (bool) old('discount_active', $service->discount_active ?? false);
  $discountType   = old('discount_type', $service->discount_type ?? 'percent');

  $startsAtVal = old(
    'discount_starts_at',
    optional($service->discount_starts_at ?? null)?->format('Y-m-d\TH:i')
  );

  $endsAtVal = old(
    'discount_ends_at',
    optional($service->discount_ends_at ?? null)?->format('Y-m-d\TH:i')
  );
@endphp

<style>
  .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:.5rem;font-weight:500;border:1px solid transparent;text-decoration:none}
  .btn-danger{background:#ef4444;color:#fff;border-color:#ef4444}
  .btn-danger:hover{background:#dc2626;border-color:#dc2626}
</style>

<div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
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
      <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
  </div>

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
      required
    >
    @error('duration_min')
      <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
  </div>

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
      required
    >
    @error('price')
      <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
  </div>

  <div class="lg:col-span-2 flex items-end">
    <label class="inline-flex items-center gap-3 p-3 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer">
      <input
        type="checkbox" name="active" value="1"
        @checked(old('active', $service->active ?? true))
        class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
      >
      <div class="flex items-center gap-2">
        <span class="text-sm font-medium text-slate-700">Servicio activo</span>
      </div>
    </label>
    <p class="text-xs text-slate-500 ml-3">
      Los servicios inactivos no estarán disponibles para nuevas citas.
    </p>
  </div>
</div>

<div class="mt-6 border-t border-slate-200 pt-6">
  <div class="flex items-center justify-between gap-3 flex-wrap mb-4">
    <div>
      <h3 class="font-semibold text-slate-800">Descuento / Promoción</h3>
      <p class="text-sm text-slate-500">
        Si activas el descuento y defines duración (días), el sistema calcula inicio/fin automáticamente.
      </p>
    </div>

    <label class="inline-flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
      <input type="checkbox" name="discount_active" value="1"
             class="w-4 h-4"
             @checked($discountActive)
             id="discount_active">
      <span class="text-sm font-medium text-slate-700">Activar descuento</span>
    </label>
  </div>

  <div id="discount_panel" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
    <div class="space-y-2">
      <label class="block text-sm font-medium text-slate-700">Tipo</label>
      <select name="discount_type" id="discount_type"
              class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        <option value="percent" @selected($discountType === 'percent')>Porcentaje (%)</option>
        <option value="fixed"   @selected($discountType === 'fixed')>Monto fijo (Bs)</option>
      </select>
      @error('discount_type') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
    </div>

    <div class="space-y-2">
      <label class="block text-sm font-medium text-slate-700">Monto</label>
      <input type="number" step="0.01" min="0"
             name="discount_amount" id="discount_amount"
             value="{{ old('discount_amount', $service->discount_amount) }}"
             class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
             placeholder="Ej: 10 o 20.00">
      @error('discount_amount') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
      <p class="text-[12px] text-slate-500" id="discount_hint"></p>
    </div>

    <div class="space-y-2">
      <label class="block text-sm font-medium text-slate-700">Duración (días)</label>
      <input type="number" min="1" max="3650"
             name="discount_duration" id="discount_duration"
             value="{{ old('discount_duration', $service->discount_duration) }}"
             class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
             placeholder="Ej: 7">
      @error('discount_duration') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
      <p class="text-[12px] text-slate-500">
        Si no defines fin manual, el fin se calcula desde el inicio + duración.
      </p>
    </div>

    <div class="lg:col-span-3">
      <details class="border border-slate-200 rounded-lg p-3 bg-slate-50">
        <summary class="cursor-pointer text-sm font-medium text-slate-700">Avanzado: definir fechas manualmente</summary>

        <div class="grid gap-4 md:grid-cols-2 mt-3">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700">Inicio</label>
            <input type="datetime-local"
                   name="discount_starts_at" id="discount_starts_at"
                   value="{{ $startsAtVal }}"
                   class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            @error('discount_starts_at') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700">Fin</label>
            <input type="datetime-local"
                   name="discount_ends_at" id="discount_ends_at"
                   value="{{ $endsAtVal }}"
                   class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            @error('discount_ends_at') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            <p class="text-[12px] text-slate-500">Si llenas fin manual, se ignora la duración.</p>
          </div>
        </div>
      </details>
    </div>
  </div>

  <script>
    (function () {
      const chk = document.getElementById('discount_active');
      const panel = document.getElementById('discount_panel');
      const type = document.getElementById('discount_type');
      const amount = document.getElementById('discount_amount');
      const duration = document.getElementById('discount_duration');
      const start = document.getElementById('discount_starts_at');
      const end = document.getElementById('discount_ends_at');
      const hint = document.getElementById('discount_hint');

      function isoLocal(d) {
        const pad = n => String(n).padStart(2, '0');
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
      }

      function setHint() {
        if (!hint || !type) return;
        hint.textContent = (type.value === 'percent')
          ? 'Porcentaje entre 0 y 100.'
          : 'Monto fijo en Bs. No debería superar el precio.';
      }

      function syncPanelVisual() {
        if (!chk || !panel) return;
        // Solo efecto visual, no deshabilita inputs (para que no te bloquee editar)
        panel.style.opacity = chk.checked ? '1' : '.55';
      }

      function ensureStart() {
        if (!start) return;
        if (!start.value) start.value = isoLocal(new Date());
      }

      function calcEndIfNeeded() {
        if (!chk?.checked) return;

        const days = parseInt(duration?.value || '0', 10);
        if (!days || days < 1) return;

        // Si fin manual existe, no se recalcula
        if (end && end.value) return;

        ensureStart();

        const startDate = new Date(start.value);
        const endDate = new Date(startDate);
        endDate.setDate(endDate.getDate() + days);

        if (end) end.value = isoLocal(endDate);
      }

      chk?.addEventListener('change', function () {
        syncPanelVisual();
        if (chk.checked) {
          ensureStart();
          calcEndIfNeeded();
        }
      });

      type?.addEventListener('change', setHint);

      duration?.addEventListener('input', function () {
        // recalcula solo si no hay fin manual
        if (end && end.value) return;
        calcEndIfNeeded();
      });

      start?.addEventListener('change', function () {
        if (end && end.value) return;
        calcEndIfNeeded();
      });

      setHint();
      syncPanelVisual();

      // Si viene activo y sin fechas, inicializa
      if (chk?.checked) {
        ensureStart();
        calcEndIfNeeded();
      }
    })();
  </script>
</div>

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
