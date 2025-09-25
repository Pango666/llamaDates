@php $isEdit = $dentist->exists; @endphp

<div class="grid gap-4 md:grid-cols-2">
  <div class="md:col-span-2">
    <label class="block text-xs text-slate-500 mb-1">Nombre *</label>
    <input name="name" value="{{ old('name',$dentist->name) }}" required class="w-full border rounded px-3 py-2">
  </div>

  <div>
    <label class="block text-xs text-slate-500 mb-1">Especialidad</label>
    <input name="specialty" value="{{ old('specialty',$dentist->specialty) }}" class="w-full border rounded px-3 py-2">
  </div>

  <div>
    <label class="block text-xs text-slate-500 mb-1">Sillón</label>
    <select name="chair_id" class="w-full border rounded px-3 py-2">
      <option value="">— Sin asignar —</option>
      @foreach($chairs as $c)
        <option value="{{ $c->id }}" @selected(old('chair_id',$dentist->chair_id)==$c->id)>{{ $c->name }}</option>
      @endforeach
    </select>
  </div>
</div>

{{-- Usuario vinculado --}}
<div class="mt-4 card">
  <h4 class="font-semibold mb-2">Usuario vinculado</h4>

  <div class="space-y-3">
    {{-- Modo A: seleccionar existente --}}
    <div class="border rounded p-3">
      <label class="inline-flex items-center gap-2">
        <input type="radio" name="create_user" value="0" {{ old('create_user') ? '' : 'checked' }}>
        <span>Seleccionar usuario existente (rol odontólogo)</span>
      </label>
      <div class="mt-2">
        <select name="user_id" class="w-full border rounded px-3 py-2">
          <option value="">— Ninguno —</option>
          @foreach($users as $u)
            <option value="{{ $u->id }}" @selected(old('user_id',$dentist->user_id)==$u->id)>{{ $u->name }} — {{ $u->email }}</option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- Modo B: crear nuevo --}}
    <div class="border rounded p-3">
      <label class="inline-flex items-center gap-2">
        <input type="radio" name="create_user" value="1" {{ old('create_user') ? 'checked' : '' }}>
        <span>Crear nuevo usuario con rol odontólogo</span>
      </label>

      <div class="grid gap-3 md:grid-cols-3 mt-2">
        <div>
          <label class="block text-xs text-slate-500 mb-1">Nombre</label>
          <input name="new_user_name" value="{{ old('new_user_name') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1">Email</label>
          <input type="email" name="new_user_email" value="{{ old('new_user_email') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1">Contraseña</label>
          <input type="password" name="new_user_password" class="w-full border rounded px-3 py-2">
        </div>
      </div>
      <p class="text-xs text-slate-500 mt-1">El usuario se creará activo con rol <strong>odontólogo</strong>.</p>
    </div>
  </div>
</div>

<div class="pt-3">
  <button class="btn btn-primary">{{ $isEdit ? 'Actualizar' : 'Guardar' }}</button>
  <a href="{{ $isEdit ? route('admin.dentists.show',$dentist) : route('admin.dentists') }}" class="btn btn-ghost">
    Cancelar
  </a>
</div>
