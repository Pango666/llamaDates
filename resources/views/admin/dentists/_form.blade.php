@php 
  $isEdit = $dentist->exists ?? false;
  $createUser = old('create_user', request()->has('create_new') ? '1' : '0');
@endphp

<div class="grid gap-6">
  {{-- Información Básica --}}
  <div class="grid gap-6 md:grid-cols-2">
    {{-- CI - Cédula de Identidad --}}
    <div class="space-y-2">
      <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Cédula de Identidad
        <span class="text-red-500">*</span>
      </label>
      <input 
        type="text" 
        name="ci" 
        value="{{ old('ci', $dentist->ci) }}" 
        required
        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
        placeholder="Ej: 12345678"
        maxlength="20"
      >
      @error('ci')
        <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ $message }}
        </p>
      @enderror
    </div>

    {{-- Nombre --}}
    <div class="space-y-2">
      <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        Nombre completo
        <span class="text-red-500">*</span>
      </label>
      <input 
        type="text" 
        name="name" 
        value="{{ old('name', $dentist->name) }}" 
        required
        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
        placeholder="Ingrese el nombre completo del odontólogo"
      >
      @error('name')
        <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ $message }}
        </p>
      @enderror
    </div>

    {{-- Dirección --}}
    <div class="space-y-2">
      <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Dirección
      </label>
      <input 
        type="text" 
        name="address" 
        value="{{ old('address', $dentist->address) }}" 
        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
        placeholder="Ingrese la dirección completa"
      >
      @error('address')
        <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ $message }}
        </p>
      @enderror
    </div>

    {{-- Especialidad --}}
    <div class="space-y-2">
      <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        Especialidad
      </label>
      <input 
        type="text" 
        name="specialty" 
        value="{{ old('specialty', $dentist->specialty) }}" 
        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
        placeholder="Ej: Ortodoncia, Periodoncia, etc."
      >
      @error('specialty')
        <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ $message }}
        </p>
      @enderror
    </div>

    {{-- Sillón --}}
    <div class="space-y-2">
      <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
        </svg>
        Sillón asignado
      </label>
      <select name="chair_id" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
        <option value="">— Sin asignar —</option>
        @foreach($chairs as $c)
          <option value="{{ $c->id }}" 
            @selected(old('chair_id', $dentist->chair_id) == $c->id)
            class="py-2">
            {{ $c->name }} @if($c->description) - {{ $c->description }} @endif
          </option>
        @endforeach
      </select>
      @error('chair_id')
        <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ $message }}
        </p>
      @enderror
    </div>
  </div>

  {{-- Estado (solo en edición) --}}
  @if($isEdit)
  <div class="flex items-center gap-2 p-3 bg-slate-50 rounded-lg">
    <input type="checkbox" name="is_active" value="1" 
      @checked(old('is_active', $dentist->is_active ?? true)) 
      class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
    <span class="text-sm font-medium text-slate-700">Odontólogo activo</span>
  </div>
  @endif

  {{-- Usuario Vinculado --}}
  <div class="border-t border-slate-200 pt-6 mt-4">
    <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
      </svg>
      Usuario del Sistema
    </h3>

    <div class="space-y-4">
      {{-- Opción 1: Usuario existente --}}
      <div class="border border-slate-300 rounded-lg p-4 hover:border-blue-500 transition-colors">
        <label class="inline-flex items-center gap-3 cursor-pointer">
          <input type="radio" name="create_user" value="0" 
            {{ $createUser == '0' ? 'checked' : '' }}
            class="text-blue-600 focus:ring-blue-500">
          <span class="font-medium text-slate-700">Vincular usuario existente</span>
        </label>
        
        <div class="mt-3 pl-7 {{ $createUser == '0' ? 'block' : 'hidden' }}" id="existing-user-section">
          <select name="user_id" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
            <option value="">— Seleccionar usuario —</option>
            @foreach($users as $u)
              <option value="{{ $u->id }}" 
                @selected(old('user_id', $dentist->user_id) == $u->id)
                class="py-2">
                {{ $u->name }} — {{ $u->email }} @if($u->dentist) (Ya vinculado) @endif
              </option>
            @endforeach
          </select>
          @error('user_id')
            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ $message }}
            </p>
          @enderror
          <p class="text-xs text-slate-500 mt-2">
            Solo se muestran usuarios con rol "odontólogo" que no estén vinculados.
          </p>
        </div>
      </div>

      {{-- Opción 2: Crear nuevo usuario --}}
      <div class="border border-slate-300 rounded-lg p-4 hover:border-blue-500 transition-colors">
        <label class="inline-flex items-center gap-3 cursor-pointer">
          <input type="radio" name="create_user" value="1" 
            {{ $createUser == '1' ? 'checked' : '' }}
            class="text-blue-600 focus:ring-blue-500">
          <span class="font-medium text-slate-700">Crear nuevo usuario</span>
        </label>

        <div class="mt-3 pl-7 {{ $createUser == '1' ? 'block' : 'hidden' }}" id="new-user-section">
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-700">Nombre completo</label>
              <input type="text" name="new_user_name"
                value="{{ old('new_user_name') }}"
                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                placeholder="Nombre del usuario">
              @error('new_user_name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
              @enderror
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-700">Email</label>
              <input type="email" name="new_user_email"
                value="{{ old('new_user_email') }}"
                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                placeholder="email@ejemplo.com">
              @error('new_user_email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
              @enderror
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-700">Celular</label>
              <input type="number" 
                name="new_user_phone"
                value="{{ old('new_user_phone') }}"
                min="60000000" 
                max="79999999"
                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                placeholder="Ej: 71234567">
              @error('new_user_phone')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
              @enderror
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-700">Contraseña {{ $isEdit ? '(opcional)' : '' }}</label>
              <input type="password" name="new_user_password"
                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                placeholder="{{ $isEdit ? 'Dejar en blanco' : 'Mín. 8 caracteres' }}">
              @error('new_user_password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
              @enderror
            </div>
          </div>
          
          <div class="mt-3 flex items-center gap-2">
            <input type="checkbox" name="send_welcome_email" value="1" 
              {{ old('send_welcome_email') ? 'checked' : '' }}
              class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            <span class="text-sm text-slate-700">Enviar email de bienvenida con credenciales</span>
          </div>
          
          <p class="text-xs text-slate-500 mt-2">
            El usuario se creará con rol <strong>odontólogo</strong> y estado activo.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Acciones del Formulario --}}
<div class="flex gap-3 pt-6 mt-6 border-t border-slate-200">
  <button 
    type="submit" 
    class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 transition-colors"
  >
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ $isEdit ? 'Actualizar Odontólogo' : 'Registrar Odontólogo' }}
  </button>
  
  <a 
    href="{{ $isEdit ? route('admin.dentists.show', $dentist) : route('admin.dentists') }}" 
    class="btn btn-ghost flex items-center gap-2 transition-colors"
  >
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
    Cancelar
  </a>
</div>