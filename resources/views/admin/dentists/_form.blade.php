@php 
  $isEdit = $dentist->exists ?? false;
  $createUser = old('create_user', request()->has('create_new') ? '1' : '0');

  // Prepare data for Pickers
  $chairItems = $chairs->map(fn($c) => [
      'id' => $c->id,
      'label' => $c->name,
      'sub' => $c->description ?? ''
  ])->values()->toArray();

  $userItems = $users->map(fn($u) => [
      'id' => $u->id,
      'label' => $u->name,
      'sub' => $u->email . ($u->dentist ? ' (Ya vinculado)' : '')
  ])->values()->toArray();
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
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2 mb-1">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            Sillón asignado
        </label>
        
        <button type="button" id="btnChair"
                class="w-full text-left border border-slate-300 rounded-lg px-4 py-2 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 transition-colors group">
            <div class="text-sm font-medium text-slate-700 group-hover:text-slate-900" id="chairLabel">
                {{ $dentist->chair ? $dentist->chair->name : '— Sin asignar —' }}
            </div>
            <div class="text-xs text-slate-400 group-hover:text-slate-500">Escribe para filtrar</div>
        </button>
        <input type="hidden" name="chair_id" id="chair_id" value="{{ old('chair_id', $dentist->chair_id) }}">

        @error('chair_id')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
      </div>
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
            id="radioExistingUser"
            {{ $createUser == '0' ? 'checked' : '' }}
            class="text-blue-600 focus:ring-blue-500">
          <span class="font-medium text-slate-700">Vincular usuario existente</span>
        </label>
        
        <div class="mt-3 pl-7 {{ $createUser == '0' ? 'block' : 'hidden' }}" id="existing-user-section">
            <button type="button" id="btnUser"
                    class="w-full text-left border border-slate-300 rounded-lg px-4 py-2 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 transition-colors group">
                <div class="text-sm font-medium text-slate-700 group-hover:text-slate-900" id="userLabel">
                    {{ $dentist->user_id ? ($dentist->user->name . ' — ' . $dentist->user->email) : '— Seleccionar usuario —' }}
                </div>
                <div class="text-xs text-slate-400 group-hover:text-slate-500">Escribe para filtrar</div>
            </button>
            <input type="hidden" name="user_id" id="user_id" value="{{ old('user_id', $dentist->user_id) }}">

            @error('user_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
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
             id="radioNewUser"
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

{{-- MODAL PICKER (Vanilla JS) --}}
<div id="pickerBackdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden z-[90] transition-opacity"></div>
<div id="pickerModal" class="fixed inset-0 hidden z-[100] flex items-center justify-center p-4">
  <div class="bg-white w-full max-w-5xl h-[90vh] rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all scale-100 opacity-100 flex flex-col">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 flex-shrink-0">
      <div>
        <div class="font-semibold text-slate-900" id="pickerTitle">Seleccionar</div>
        <div class="text-xs text-slate-500" id="pickerSubtitle">Escribe para filtrar opciones</div>
      </div>
      <button type="button" id="pickerClose" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <div class="p-4 space-y-3 flex-shrink-0">
      <div class="relative">
        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input id="pickerSearch" type="text"
               class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-400"
               placeholder="Buscar..." autocomplete="off">
      </div>
    </div>
    
    <div class="flex-1 overflow-y-auto overflow-x-hidden p-4 custom-scrollbar">
       <div id="pickerList" class="space-y-1 pr-1"></div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // ELEMENTOS DOM
  const btnChair = document.getElementById('btnChair');
  const btnUser = document.getElementById('btnUser');

  const chairId = document.getElementById('chair_id');
  const userId = document.getElementById('user_id');

  const chairLabel = document.getElementById('chairLabel');
  const userLabel = document.getElementById('userLabel');

  // MODAL
  const backdrop = document.getElementById('pickerBackdrop');
  const modal    = document.getElementById('pickerModal');
  const closeBtn = document.getElementById('pickerClose');
  const titleEl  = document.getElementById('pickerTitle');
  const searchEl = document.getElementById('pickerSearch');
  const listEl   = document.getElementById('pickerList');
  
  // TOGGLE SECTIONS
  const radioExisting = document.getElementById('radioExistingUser');
  const radioNew = document.getElementById('radioNewUser');
  const sectionExisting = document.getElementById('existing-user-section');
  const sectionNew = document.getElementById('new-user-section');

  if(radioExisting && radioNew) {
      radioExisting.addEventListener('change', () => {
          if(radioExisting.checked) {
              sectionExisting.classList.remove('hidden');
              sectionExisting.classList.add('block');
              sectionNew.classList.remove('block');
              sectionNew.classList.add('hidden');
          }
      });
      radioNew.addEventListener('change', () => {
          if(radioNew.checked) {
              sectionNew.classList.remove('hidden');
              sectionNew.classList.add('block');
              sectionExisting.classList.remove('block');
              sectionExisting.classList.add('hidden');
          }
      });
  }

  // DATA
  const CHAIRS = @json($chairItems);
  const USERS = @json($userItems);

  let currentType = null; // 'chair'|'user'

  // FUNCIONES PICKER
  function openPicker(type) {
    currentType = type;
    searchEl.value = '';
    
    if(type==='chair') titleEl.textContent = 'Seleccionar Sillón';
    if(type==='user') titleEl.textContent = 'Seleccionar Usuario';

    modal.classList.remove('hidden');
    backdrop.classList.remove('hidden');
    
    // Animar entrada
    setTimeout(() => {
        searchEl.focus();
    }, 50);

    renderList();
  }

  function closePicker() {
    modal.classList.add('hidden');
    backdrop.classList.add('hidden');
    currentType = null;
  }

  function renderList() {
    let items = [];
    if(currentType==='chair') items = CHAIRS;
    if(currentType==='user') items = USERS;

    const q = searchEl.value.toLowerCase().trim();
    
    if(q) {
        items = items.filter(i => 
            (i.label||'').toLowerCase().includes(q) || 
            (i.sub||'').toLowerCase().includes(q)
        );
    }

    listEl.innerHTML = '';
    
    if(items.length === 0) {
        listEl.innerHTML = `<div class="p-8 text-center text-slate-500 text-sm">No se encontraron resultados</div>`;
        return;
    }

    items.forEach(item => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'w-full text-left px-4 py-3 hover:bg-blue-50 rounded-xl transition-colors group border border-transparent hover:border-blue-100';
        btn.innerHTML = `
            <div class="font-medium text-slate-800 group-hover:text-blue-700">${item.label}</div>
            ${item.sub ? `<div class="text-xs text-slate-400 group-hover:text-blue-500">${item.sub}</div>` : ''}
        `;
        
        btn.onclick = () => selectItem(item);
        listEl.appendChild(btn);
    });
  }

  function selectItem(item) {
    if(currentType==='chair') {
        chairId.value = item.id;
        chairLabel.textContent = item.label;
        chairLabel.classList.add('text-slate-900');
    }
    if(currentType==='user') {
        userId.value = item.id;
        userLabel.textContent = item.label;
        userLabel.classList.add('text-slate-900');
    }
    closePicker();
  }

  // EVENTOS
  if(btnChair) btnChair.onclick = () => openPicker('chair');
  if(btnUser) btnUser.onclick = () => openPicker('user');
  
  if(closeBtn) closeBtn.onclick = closePicker;
  if(backdrop) backdrop.onclick = closePicker;
  if(searchEl) searchEl.oninput = renderList;
});
</script>