@php($p = $product ?? null)
<div>
  <label class="text-xs text-slate-500">SKU</label>
  <input name="sku" value="{{ old('sku', $p->sku ?? '') }}" class="border rounded px-3 py-2">
</div>
<div>
  <label class="text-xs text-slate-500">Nombre</label>
  <input name="name" value="{{ old('name', $p->name ?? '') }}" class="border rounded px-3 py-2" required>
</div>
<div>
  <label class="text-xs text-slate-500">Unidad</label>
  <input name="unit" value="{{ old('unit', $p->unit ?? 'und') }}" class="border rounded px-3 py-2" required>
</div>
<div>
  <label class="text-xs text-slate-500">Precio venta (Bs)</label>
  <input name="sell_price" type="number" step="0.01" min="0" value="{{ old('sell_price', $p->sell_price ?? 0) }}" class="border rounded px-3 py-2" required>
</div>
<div>
  <label class="text-xs text-slate-500">Costo promedio (Bs)</label>
  <input name="cost_avg" type="number" step="0.0001" min="0" value="{{ old('cost_avg', $p->cost_avg ?? 0) }}" class="border rounded px-3 py-2">
</div>
<div>
  <label class="text-xs text-slate-500">Stock mínimo</label>
  <input name="min_stock" type="number" step="0.0001" min="0" value="{{ old('min_stock', $p->min_stock ?? 0) }}" class="border rounded px-3 py-2">
</div>
<div class="md:col-span-3">
  <label class="inline-flex items-center gap-2">
    <input type="checkbox" name="active" value="1" @checked(old('active', $p->active ?? true))>
    Activo
  </label>
</div>
