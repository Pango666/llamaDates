@php($l = $location ?? null)
<div>
  <label class="text-xs text-slate-500">Código</label>
  <input name="code" value="{{ old('code',$l->code ?? '') }}" class="border rounded px-3 py-2">
</div>
<div>
  <label class="text-xs text-slate-500">Nombre</label>
  <input name="name" value="{{ old('name',$l->name ?? '') }}" class="border rounded px-3 py-2" required>
</div>
<div>
  <label class="inline-flex items-center gap-2 mt-6">
    <input type="checkbox" name="is_main" value="1" @checked(old('is_main',$l->is_main ?? false))> Principal
  </label>
</div>
<div>
  <label class="inline-flex items-center gap-2 mt-6">
    <input type="checkbox" name="active" value="1" @checked(old('active',$l->active ?? true))> Activa
  </label>
</div>
