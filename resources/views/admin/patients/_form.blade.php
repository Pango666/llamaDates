@php
  $isEdit = $patient->exists;
@endphp

<div class="grid gap-4 md:grid-cols-2">
  <div>
    <label class="block text-xs text-slate-500 mb-1">Nombres *</label>
    <input name="first_name" value="{{ old('first_name',$patient->first_name) }}" required
           class="w-full border rounded px-3 py-2">
  </div>
  <div>
    <label class="block text-xs text-slate-500 mb-1">Apellidos *</label>
    <input name="last_name" value="{{ old('last_name',$patient->last_name) }}" required
           class="w-full border rounded px-3 py-2">
  </div>

  <div>
    <label class="block text-xs text-slate-500 mb-1">CI / Documento</label>
    <input name="ci" value="{{ old('ci',$patient->ci) }}" class="w-full border rounded px-3 py-2">
  </div>
  <div>
    <label class="block text-xs text-slate-500 mb-1">Fecha de nacimiento</label>
    <input type="date" name="birthdate" value="{{ old('birthdate',$patient->birthdate) }}"
           class="w-full border rounded px-3 py-2">
  </div>

  <div>
    <label class="block text-xs text-slate-500 mb-1">Email</label>
    <input type="email" name="email" value="{{ old('email',$patient->email) }}"
           class="w-full border rounded px-3 py-2">
  </div>
  <div>
    <label class="block text-xs text-slate-500 mb-1">Teléfono</label>
    <input name="phone" value="{{ old('phone',$patient->phone) }}"
           class="w-full border rounded px-3 py-2">
  </div>

  <div class="md:col-span-2">
    <label class="block text-xs text-slate-500 mb-1">Dirección</label>
    <textarea name="address" rows="2" class="w-full border rounded px-3 py-2">{{ old('address',$patient->address) }}</textarea>
  </div>
</div>

<div class="pt-3">
  <button class="btn btn-primary">{{ $isEdit ? 'Actualizar' : 'Guardar' }}</button>
  <a href="{{ $isEdit ? route('admin.patients.show',$patient) : route('admin.patients') }}" class="btn btn-ghost">Cancelar</a>
</div>
