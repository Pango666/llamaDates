<div class="grid md:grid-cols-2 gap-4">
  <div>
    <label class="text-xs text-slate-500">Nombre</label>
    <input name="name" value="{{ old('name', $role->name ?? '') }}" class="w-full border rounded px-3 py-2" required>
  </div>
  <div>
    <label class="text-xs text-slate-500">Etiqueta</label>
    <input name="label" value="{{ old('label', $role->label ?? '') }}" class="w-full border rounded px-3 py-2">
  </div>
</div>
