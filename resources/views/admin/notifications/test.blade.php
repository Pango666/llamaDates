@extends('layouts.app')
@section('title', 'Enviar Prueba · Notificaciones')

@section('header-actions')
  <a href="{{ route('admin.notifications.index') }}" class="btn btn-ghost text-slate-600 hover:text-slate-800 flex items-center gap-2 transition-colors rounded-xl px-4 py-2 text-sm font-medium">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver al Historial
  </a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
          </svg>
          Enviar Notificación de Prueba
        </h1>
        <p class="text-sm text-slate-600 mt-1">
            Envíe mensajes manuales para verificar la funcionalidad del sistema (Email y Push).
        </p>
      </div>
    </div>

    @if(session('ok'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center gap-3">
            <div class="bg-emerald-100 p-2 rounded-lg">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <p class="font-bold">¡Enviado con éxito!</p>
                <p class="text-sm text-emerald-600">{{ session('ok') }}</p>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.notifications.sendTest') }}" method="POST" class="card space-y-8">
        @csrf
        
        <div class="grid md:grid-cols-2 gap-8">
            
            {{-- SECCIÓN 1: DESTINATARIO (MODAL PICKER) --}}
            <div class="md:col-span-2 space-y-3">
                <label class="block text-sm font-medium text-slate-700">1. Destinatario <span class="text-red-500">*</span></label>
                
                <button type="button" id="btnUser" class="w-full text-left border border-slate-300 rounded-xl px-4 py-3 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all group">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium text-slate-700 group-hover:text-slate-900" id="userLabel">Seleccionar usuario...</div>
                            <div class="text-xs text-slate-400 group-hover:text-slate-500" id="userSubLabel">Click para buscar</div>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>
                <input type="hidden" name="user_id" id="user_id" required>
                @error('user_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                <p class="text-xs text-slate-500 pl-1">Seleccione el usuario al que desea enviar la prueba.</p>
            </div>

            {{-- SECCIÓN 2: CANALES --}}
            <div class="md:col-span-2 space-y-3">
                <label class="block text-sm font-medium text-slate-700">2. Canales de Envío <span class="text-red-500">*</span></label>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Email Option --}}
                    <label class="cursor-pointer relative">
                        <input type="checkbox" name="channels[]" value="email" class="peer sr-only" checked>
                        <div class="p-4 border border-slate-200 rounded-xl bg-slate-50 peer-checked:bg-blue-50 peer-checked:border-blue-500 peer-checked:ring-1 peer-checked:ring-blue-500 transition-all flex items-center gap-3 hover:border-blue-300">
                            <div class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 peer-checked:text-blue-600 peer-checked:border-blue-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <div class="font-semibold text-slate-900 peer-checked:text-blue-800">Email</div>
                                <div class="text-xs text-slate-500 peer-checked:text-blue-600">Enviar correo electrónico</div>
                            </div>
                            <div class="ml-auto opacity-0 peer-checked:opacity-100 text-blue-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                    </label>

                    {{-- Push Option --}}
                    <label class="cursor-pointer relative">
                        <input type="checkbox" name="channels[]" value="push" class="peer sr-only" checked>
                        <div class="p-4 border border-slate-200 rounded-xl bg-slate-50 peer-checked:bg-purple-50 peer-checked:border-purple-500 peer-checked:ring-1 peer-checked:ring-purple-500 transition-all flex items-center gap-3 hover:border-purple-300">
                            <div class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 peer-checked:text-purple-600 peer-checked:border-purple-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <div class="font-semibold text-slate-900 peer-checked:text-purple-800">Push Notification</div>
                                <div class="text-xs text-slate-500 peer-checked:text-purple-600">Enviar al móvil</div>
                            </div>
                            <div class="ml-auto opacity-0 peer-checked:opacity-100 text-purple-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                    </label>

                    {{-- WhatsApp Option --}}
                    <label class="cursor-pointer relative md:col-span-2 lg:col-span-1">
                        <input type="checkbox" name="channels[]" value="whatsapp" class="peer sr-only">
                        <div class="p-4 border border-slate-200 rounded-xl bg-slate-50 peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:ring-1 peer-checked:ring-emerald-500 transition-all flex items-center gap-3 hover:border-emerald-300">
                            <div class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 peer-checked:text-emerald-600 peer-checked:border-emerald-200">
                                <svg class="w-6 h-6 text-emerald-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold text-slate-900 peer-checked:text-emerald-800">WhatsApp</div>
                                <div class="text-xs text-slate-500 peer-checked:text-emerald-600">Enviar por el Bot</div>
                            </div>
                            <div class="ml-auto opacity-0 peer-checked:opacity-100 text-emerald-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="hidden md:block col-span-2 border-t border-slate-200 my-2"></div>

            {{-- SECCIÓN 3: MENSAJE --}}
            <div class="md:col-span-2 space-y-4">
                <label class="block text-sm font-medium text-slate-700">3. Contenido</label>
                
                <div>
                    <label for="title" class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1 block">Título</label>
                    <input type="text" name="title" id="title" class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/20 shadow-sm" placeholder="Ej: Recordatorio Importante" required>
                </div>

                <div>
                    <label for="body" class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1 block">Mensaje</label>
                    <textarea name="body" id="body" rows="4" class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/20 shadow-sm" placeholder="Escribe el cuerpo del mensaje..." required></textarea>
                </div>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 mt-6 md:col-span-2">
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-ghost rounded-xl px-6">Cancelar</a>
            <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-500/30 rounded-xl px-8 py-3 flex items-center gap-2 font-medium transition-transform hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Enviar Notificación
            </button>
        </div>
    </form>
</div>

{{-- MODAL PICKER (USERS) --}}
<div id="pickerBackdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden z-50 transition-opacity"></div>
<div id="pickerModal" class="fixed inset-0 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all scale-100 opacity-100 flex flex-col max-h-[80vh]">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 flex-shrink-0">
            <div>
                <div class="font-bold text-slate-900 text-lg">Seleccionar Usuario</div>
                <div class="text-xs text-slate-500" id="pickerSubtitle">Buscar por nombre o email</div>
            </div>
            <button type="button" id="pickerClose" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-4 space-y-3 flex-shrink-0">
            <div class="relative">
                <svg class="w-5 h-5 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input id="pickerSearch" type="text"
                         class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-400 bg-slate-50 focus:bg-white"
                         placeholder="Buscar usuario..." autocomplete="off">
            </div>
        </div>

        <div id="pickerList" class="overflow-y-auto p-2 pt-0 space-y-1 custom-scrollbar flex-grow">
            {{-- Lista dinámica --}}
        </div>
        
        <div class="p-3 border-t border-slate-100 bg-slate-50 text-center text-xs text-slate-400 flex-shrink-0">
            Mostrando primeros 50 resultados
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
</style>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    let allUsers = []; 

    // DOM Elements
    const btnUser    = document.getElementById('btnUser');
    const userId     = document.getElementById('user_id');
    const userLabel  = document.getElementById('userLabel');
    const userSub    = document.getElementById('userSubLabel');

    // Modal Elements
    const backdrop   = document.getElementById('pickerBackdrop');
    const modal      = document.getElementById('pickerModal');
    const closeBtn   = document.getElementById('pickerClose');
    const searchEl   = document.getElementById('pickerSearch');
    const listEl     = document.getElementById('pickerList');

    // --- Logic ---

    // Fetch Users
    async function fetchUsers() {
        listEl.innerHTML = '<div class="p-8 text-center text-slate-400 flex flex-col items-center gap-2"><svg class="w-8 h-8 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg><span>Cargando usuarios...</span></div>';
        
        try {
            const res = await fetch("{{ route('admin.notifications.users_search') }}");
            if(!res.ok) throw new Error('Network error');
            allUsers = await res.json();
            renderList('');
        } catch(e) {
            console.error(e);
            listEl.innerHTML = '<div class="p-4 text-center text-red-500 text-sm">Error al cargar usuarios. Intente recargar.</div>';
        }
    }

    // Render List
    function renderList(filterText = '') {
        const q = filterText.toLowerCase().trim();
        const filtered = allUsers.filter(u => {
             const name  = (u.name || '').toLowerCase();
             const email = (u.email || '').toLowerCase();
             return name.includes(q) || email.includes(q); 
        });

        listEl.innerHTML = '';
        
        if(filtered.length === 0) {
            listEl.innerHTML = '<div class="p-8 text-center text-slate-500">No se encontraron usuarios.</div>';
            return;
        }

        filtered.forEach(u => {
            const btn = document.createElement('button');
            btn.type  = 'button';
            btn.className = 'w-full text-left px-4 py-3 hover:bg-blue-50 rounded-xl transition-all group border border-transparent hover:border-blue-100 flex items-center justify-between gap-3';
            
            // Avatar logic
            const initials = u.name.substring(0,2).toUpperCase();
            
            btn.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-sm group-hover:bg-blue-200 group-hover:text-blue-700 transition-colors">
                        ${initials}
                    </div>
                    <div>
                        <div class="font-medium text-slate-800 group-hover:text-blue-700 text-sm md:text-base">${u.name}</div>
                        <div class="text-xs text-slate-400 group-hover:text-blue-500">${u.email}</div>
                    </div>
                </div>
                <div class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-500 group-hover:bg-blue-100 group-hover:text-blue-600">
                    ${u.role ? u.role.name : 'Usuario'}
                </div>
            `;
            
            btn.onclick = () => selectUser(u);
            listEl.appendChild(btn);
        });
    }

    // Select User
    function selectUser(u) {
        userId.value = u.id;
        userLabel.textContent = u.name;
        userLabel.classList.add('text-slate-900', 'font-bold');
        userSub.textContent   = u.email;
        closeModal();
    }

    // Modal Actions
    function openModal() {
        modal.classList.remove('hidden');
        backdrop.classList.remove('hidden');
        searchEl.value = '';
        setTimeout(() => searchEl.focus(), 50);
        
        if(allUsers.length === 0) {
            fetchUsers();
        } else {
            renderList('');
        }
    }

    function closeModal() {
        modal.classList.add('hidden');
        backdrop.classList.add('hidden');
    }

    // Bindings
    btnUser.onclick    = openModal;
    closeBtn.onclick   = closeModal;
    backdrop.onclick   = closeModal;
    searchEl.oninput   = (e) => renderList(e.target.value);
    
    document.addEventListener('keydown', (e) => {
        if(e.key === 'Escape') closeModal();
    });
});
</script>
@endsection
