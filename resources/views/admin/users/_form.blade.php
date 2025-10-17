<div class="space-y-6">
  {{-- Información Básica --}}
  <div>
    <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
      </svg>
      Información Básica
    </h3>
    
    <div class="grid md:grid-cols-2 gap-6">
      {{-- Nombre --}}
      <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          Nombre Completo <span class="text-red-500">*</span>
        </label>
        <input 
          type="text" 
          name="name" 
          value="{{ old('name', $user->name ?? '') }}" 
          class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          placeholder="Nombre del usuario"
          required
        >
      </div>

      {{-- Email --}}
      <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          Correo Electrónico <span class="text-red-500">*</span>
        </label>
        <input 
          type="email" 
          name="email" 
          value="{{ old('email', $user->email ?? '') }}" 
          class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          placeholder="usuario@ejemplo.com"
          required
        >
      </div>

      {{-- Teléfono --}}
      <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
          </svg>
          Teléfono
        </label>
        <input 
          type="text" 
          name="phone" 
          value="{{ old('phone', $user->phone ?? '') }}" 
          class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          placeholder="Número de teléfono"
        >
      </div>

      {{-- Estado --}}
      <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Estado del Usuario
        </label>
        <select 
          name="status" 
          class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
        >
          @foreach(['active' => 'Activo', 'suspended' => 'Suspendido'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $user->status ?? 'active') === $value)>
              {{ $label }}
            </option>
          @endforeach
        </select>
      </div>
    </div>
  </div>

  {{-- Configuración de Acceso --}}
  <div>
    <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
      </svg>
      Configuración de Acceso
    </h3>
    
    <div class="grid md:grid-cols-2 gap-6">
      {{-- Rol Principal --}}
      <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          Rol Principal
        </label>
        <select 
          name="role" 
          class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
        >
          @foreach(['admin' => 'Administrador', 'asistente' => 'Asistente', 'odontologo' => 'Odontólogo', 'paciente' => 'Paciente'] as $value => $label)
            <option value="{{ $value }}" @selected(old('role', $user->role ?? 'asistente') === $value)>
              {{ $label }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- Contraseña --}}
      <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
          Contraseña @if(!$user) <span class="text-red-500">*</span> @endif
        </label>
        <input 
          type="password" 
          name="password" 
          class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          placeholder="{{ $user ? 'Dejar en blanco para no cambiar' : 'Contraseña del usuario' }}"
          @if(!$user) required @endif
        >
        @if($user)
          <p class="text-xs text-slate-500">Dejar en blanco para mantener la contraseña actual.</p>
        @endif
      </div>
    </div>
  </div>

  {{-- Roles y Permisos --}}
  <div>
    <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
      </svg>
      Roles y Permisos
    </h3>
    
    <div class="grid md:grid-cols-2 gap-6">
      {{-- Roles --}}
      <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700">Roles Adicionales</label>
        <div class="border border-slate-300 rounded-lg p-4 max-h-48 overflow-y-auto bg-white">
          @foreach($roles as $role)
            <label class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded transition-colors">
              <input 
                type="checkbox" 
                name="roles[]" 
                value="{{ $role->id }}"
                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                @checked(in_array($role->id, old('roles', isset($user) ? $user->roles->pluck('id')->all() : [])))
              >
              <span class="text-sm text-slate-700">{{ $role->name }}</span>
            </label>
          @endforeach
        </div>
        <p class="text-xs text-slate-500">Seleccione los roles adicionales para el usuario.</p>
      </div>

      {{-- Permisos Directos --}}
      <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700">Permisos Directos</label>
        <div class="border border-slate-300 rounded-lg p-4 max-h-48 overflow-y-auto bg-white">
          @foreach($perms as $permission)
            <label class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded transition-colors">
              <input 
                type="checkbox" 
                name="perms[]" 
                value="{{ $permission->id }}"
                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                @checked(in_array($permission->id, old('perms', isset($user) ? $user->permissions->pluck('id')->all() : [])))
              >
              <span class="text-sm text-slate-700">{{ $permission->name }}</span>
            </label>
          @endforeach
        </div>
        <p class="text-xs text-slate-500">Permisos específicos adicionales al rol principal.</p>
      </div>
    </div>
  </div>
</div>