@props([
    'name',
    'label' => null,
    'options' => [], // Array of [{id, label, sub}]
    'value' => '',   // Selected ID
    'text' => 'Seleccionar...', // Selected text
    'title' => 'Seleccionar opción'
])

@php
    $uniqId = 'picker_' . $name . '_' . uniqid();
    $optionsJson = json_encode($options);
    // Determine initial text
    $initialText = $text;
    if($value) {
        // Try to find label in options
        foreach($options as $opt) {
            if($opt['id'] == $value) {
                $initialText = $opt['label'];
                break;
            }
        }
    }
@endphp

<div class="space-y-2" id="{{ $uniqId }}">
    @if($label)
        <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
            {{ $slot }}
            {{ $label }}
        </label>
    @endif

    {{-- Trigger --}}
    <button type="button" 
            class="trigger-btn w-full text-left border border-slate-300 rounded-lg px-4 py-3 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 transition-colors group">
        <div class="font-medium text-slate-700 group-hover:text-slate-900 selected-label">
            {{ $initialText }}
        </div>
    </button>
    
    <input type="hidden" name="{{ $name }}" class="hidden-input" value="{{ $value }}">
    <p class="text-xs text-slate-500 mt-1 helper-text"></p>

    {{-- Modal (Initially Hidden) --}}
    <div class="modal-backdrop fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 hidden">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all scale-100 opacity-100 flex flex-col max-h-[80vh]">
             
            {{-- Header --}}
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 flex-shrink-0">
                <div>
                    <div class="font-semibold text-slate-900">{{ $title }}</div>
                    <div class="text-xs text-slate-500">Escribe para filtrar opciones</div>
                </div>
                <button type="button" class="close-btn text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Search --}}
            <div class="p-4 space-y-3 flex-shrink-0">
                <div class="relative">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" 
                           class="search-input w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-400"
                           placeholder="Buscar..." 
                           autocomplete="off">
                </div>
            </div>

            {{-- List --}}
            <div class="list-container overflow-y-auto p-2 pt-0 space-y-1 custom-scrollbar flex-grow">
                {{-- Items injected here --}}
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const container = document.getElementById('{{ $uniqId }}');
    const options = {!! $optionsJson !!};
    
    const triggerBtn = container.querySelector('.trigger-btn');
    const labelEl = container.querySelector('.selected-label');
    const hiddenInput = container.querySelector('.hidden-input');
    const modal = container.querySelector('.modal-backdrop');
    const closeBtn = container.querySelector('.close-btn');
    const searchInput = container.querySelector('.search-input');
    const listContainer = container.querySelector('.list-container');

    // MOVER AL BODY PARA EVITAR PROBLEMAS DE Z-INDEX
    document.body.appendChild(modal);

    function open() {
        modal.classList.remove('hidden');
        renderList('');
        setTimeout(() => searchInput.focus(), 50);
    }

    function close() {
        modal.classList.add('hidden');
    }

    function select(item) {
        hiddenInput.value = item.id;
        labelEl.textContent = item.label;
        labelEl.classList.add('text-slate-900');
        close();
    }

    function renderList(q) {
        q = q.toLowerCase();
        const filtered = options.filter(i => 
            (i.label || '').toLowerCase().includes(q) || 
            (i.sub || '').toLowerCase().includes(q)
        );

        listContainer.innerHTML = '';
        
        if(filtered.length === 0) {
            listContainer.innerHTML = `<div class="p-8 text-center text-slate-500 text-sm">No se encontraron resultados</div>`;
            return;
        }

        filtered.forEach(item => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'w-full text-left px-4 py-3 hover:bg-blue-50 rounded-xl transition-colors group border border-transparent hover:border-blue-100 flex justify-between items-center';
            
            const sub = item.sub ? `<span class="text-xs text-slate-400 group-hover:text-blue-500">${item.sub}</span>` : '';
            
            btn.innerHTML = `
                <span class="font-medium text-slate-800 group-hover:text-blue-700">${item.label}</span>
                ${sub}
            `;
            btn.onclick = () => select(item);
            listContainer.appendChild(btn);
        });
    }

    triggerBtn.onclick = open;
    closeBtn.onclick = close;
    modal.onclick = (e) => {
        if(e.target === modal) close();
    };
    searchInput.oninput = (e) => renderList(e.target.value);
    
    // keydown escape
    document.addEventListener('keydown', (e) => {
        if(e.key === 'Escape' && !modal.classList.contains('hidden')) close();
    });

})();
</script>
