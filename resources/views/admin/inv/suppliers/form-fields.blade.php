@php($s = $supplier ?? null)
<div>
  <label class="text-xs text-slate-500">Nombre</label>
  <input name="name" value="{{ old('name',$s->name ?? '') }}" class="border rounded px-3 py-2" required>
</div>
<div>
  <label class="text-xs text-slate-500">Email</label>
  <input name="email" type="email" value="{{ old('email',$s->email ?? '') }}" class="border rounded px-3 py-2">
</div>
<div>
  <label class="text-xs text-slate-500">Teléfono</label>
  <input name="phone" value="{{ old('phone',$s->phone ?? '') }}" class="border rounded px-3 py-2">
</div>
<div class="md:col-span-2">
  <label class="text-xs text-slate-500">Notas</label>
  <textarea name="notes" rows="3" class="border rounded px-3 py-2 w-full">{{ old('notes',$s->notes ?? '') }}</textarea>
</div>
<div class="md:col-span-2">
  <label class="inline-flex items-center gap-2">
    <input type="checkbox" name="active" value="1" @checked(old('active',$s->active ?? true))> Activo
  </label>
</div>
