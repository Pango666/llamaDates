@php $isEdit = $service->exists; @endphp

<div class="grid gap-4 md:grid-cols-3">
  <div class="md:col-span-2">
    <label class="block text-xs text-slate-500 mb-1">Nombre *</label>
    <input name="name" value="{{ old('name',$service->name) }}" required class="w-full border rounded px-3 py-2">
  </div>
  <div>
    <label class="block text-xs text-slate-500 mb-1">Duración (minutos) *</label>
    <input type="number" min="5" max="480" step="5" name="duration_min"
           value="{{ old('duration_min',$service->duration_min) }}"
           class="w-full border rounded px-3 py-2">
  </div>
  <div>
    <label class="block text-xs text-slate-500 mb-1">Precio *</label>
    <input type="number" min="0" step="0.01" name="price" value="{{ old('price',$service->price) }}"
           class="w-full border rounded px-3 py-2">
  </div>
  <div class="flex items-end">
    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="active" value="1" @checked(old('active',$service->active))>
      <span>Activo</span>
    </label>
  </div>
</div>

<div class="pt-3">
  <button class="btn btn-primary">{{ $isEdit ? 'Actualizar' : 'Guardar' }}</button>
  <a href="{{ route('admin.services') }}" class="btn btn-ghost">Cancelar</a>
</div>
