<div class="grid md:grid-cols-2 gap-4">
  <div>
    <label class="text-xs text-slate-500">Nombre (clave)</label>
    <input name="name" value="{{ old('name', $permission->name ?? '') }}" class="w-full border rounded px-3 py-2" required placeholder="inventory.view">
  </div>
  <div>
    <label class="text-xs text-slate-500">Etiqueta</label>
    <input name="label" value="{{ old('label', $permission->label ?? '') }}" class="w-full border rounded px-3 py-2" placeholder="Ver inventario">
  </div>
</div>
