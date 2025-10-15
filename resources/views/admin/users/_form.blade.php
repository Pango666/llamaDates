<div class="grid md:grid-cols-2 gap-4">
  <div>
    <label class="text-xs text-slate-500">Nombre</label>
    <input name="name" value="{{ old('name', $user->name ?? '') }}" class="w-full border rounded px-3 py-2" required>
  </div>
  <div>
    <label class="text-xs text-slate-500">Email</label>
    <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="w-full border rounded px-3 py-2" required>
  </div>

  <div>
    <label class="text-xs text-slate-500">Teléfono</label>
    <input name="phone" value="{{ old('phone', $user->phone ?? '') }}" class="w-full border rounded px-3 py-2">
  </div>
  <div>
    <label class="text-xs text-slate-500">Estado</label>
    <select name="status" class="w-full border rounded px-3 py-2">
      @foreach(['active'=>'Activo','suspended'=>'Suspendido'] as $val=>$lbl)
        <option value="{{ $val }}" @selected(old('status', $user->status ?? 'active')===$val)>{{ $lbl }}</option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="text-xs text-slate-500">Rol (enum principal)</label>
    <select name="role" class="w-full border rounded px-3 py-2">
      @foreach(['admin','asistente','odontologo','paciente'] as $r)
        <option value="{{ $r }}" @selected(old('role', $user->role ?? 'asistente')===$r)>{{ ucfirst($r) }}</option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="text-xs text-slate-500">Password @if(!$user) <span class="text-slate-400">(requerido)</span> @endif</label>
    <input type="password" name="password" class="w-full border rounded px-3 py-2" @if(!$user) required @endif>
    @if($user)<small class="text-xs text-slate-500">Dejar en blanco para no cambiar.</small>@endif
  </div>
</div>

<div class="grid md:grid-cols-2 gap-4">
  <div>
    <label class="text-xs text-slate-500">Roles (tabla roles)</label>
    <div class="border rounded p-2 max-h-48 overflow-auto">
      @foreach($roles as $r)
        <label class="flex items-center gap-2">
          <input type="checkbox" name="roles[]" value="{{ $r->id }}"
            @checked(in_array($r->id, old('roles', isset($user) ? $user->roles->pluck('id')->all() : [])))>
          <span>{{ $r->name }}</span>
        </label>
      @endforeach
    </div>
  </div>
  <div>
    <label class="text-xs text-slate-500">Permisos directos</label>
    <div class="border rounded p-2 max-h-48 overflow-auto">
      @foreach($perms as $p)
        <label class="flex items-center gap-2">
          <input type="checkbox" name="perms[]" value="{{ $p->id }}"
            @checked(in_array($p->id, old('perms', isset($user) ? $user->permissions->pluck('id')->all() : [])))>
          <span>{{ $p->name }}</span>
        </label>
      @endforeach
    </div>
  </div>
</div>
