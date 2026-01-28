@props([
    'id' => 'picker-' . uniqid(),
    'label' => 'Seleccionar',
    'placeholder' => 'Seleccione una opción',
    'name',
    'value' => null,
    'items' => [], // Collection or Array with ['id', 'label', 'sub']
    'required' => false
])

@php
    $selectedItem = null;
    $itemsJson = collect($items)->map(fn($i) => [
        'id' => (string)$i['id'],
        'label' => $i['label'],
        'sub' => $i['sub'] ?? ''
    ])->values();

    if($value) {
        $selectedItem = $itemsJson->firstWhere('id', (string)$value);
    }
    
    $displayText = $selectedItem ? $selectedItem['label'] : $placeholder;
@endphp

<div x-data="{
    items: @js($itemsJson),
    filteredItems: @js($itemsJson),
    selectedValue: @js($value),
    selectedLabel: null,
    isOpen: false,
    search: '',
    id: @js($id),

    init() {
        if (this.selectedValue) {
            const found = this.items.find(i => i.id == this.selectedValue);
            if (found) this.selectedLabel = found.label;
        }
        this.$watch('search', val => {
            const q = val.toLowerCase().trim();
            if (!q) {
                this.filteredItems = this.items;
            } else {
                this.filteredItems = this.items.filter(i => 
                    (i.label || '').toLowerCase().includes(q) || 
                    (i.sub || '').toLowerCase().includes(q)
                );
            }
        });
    },

    openPicker() {
        this.isOpen = true;
        this.search = '';
        this.filteredItems = this.items;
        this.$nextTick(() => {
            if(this.$refs.searchInput) this.$refs.searchInput.focus();
        });
    },

    closePicker() {
        this.isOpen = false;
    },

    selectItem(item) {
        this.selectedValue = item.id;
        this.selectedLabel = item.label;
        this.closePicker();
    }
}" class="relative">
    <label class="block text-sm font-medium text-slate-700 flex items-center gap-2 mb-2">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>

    {{-- Trigger Button --}}
    <button type="button" x-on:click="openPicker()"
            class="w-full text-left border border-slate-300 rounded-lg px-4 py-2 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 transition-colors group flex items-center justify-between">
        <div>
            <div class="text-sm font-medium text-slate-700 group-hover:text-slate-900" x-text="selectedLabel || '{{ $placeholder }}'">
                {{ $displayText }}
            </div>
            <div class="text-xs text-slate-400 group-hover:text-slate-500" x-show="!selectedLabel">
                Escribe para filtrar
            </div>
        </div>
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>

    {{-- Hidden Input --}}
    <input type="hidden" name="{{ $name }}" :value="selectedValue">

    {{-- Modal --}}
    <div x-show="isOpen" 
         x-transition.opacity
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[100] flex items-center justify-center p-4"
         style="display: none;"
    >
        <div @@click.outside="closePicker()" 
             class="bg-white w-full max-w-5xl h-[90vh] rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col"
        >
            {{-- Header --}}
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <div class="font-semibold text-slate-900">{{ $label }}</div>
                    <div class="text-xs text-slate-500">Filtrar opciones</div>
                </div>
                <button type="button" x-on:click="closePicker()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Search --}}
            <div class="p-4 border-b border-slate-100">
                <div class="relative">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input x-model="search" x-ref="searchInput"
                           type="text"
                           class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-400"
                           placeholder="Buscar..." autocomplete="off">
                </div>
            </div>

            {{-- List --}}
            <div class="overflow-y-auto p-2 space-y-1 custom-scrollbar flex-1">
                <template x-for="item in filteredItems" :key="item.id">
                    <button type="button" x-on:click="selectItem(item)"
                            class="w-full text-left px-4 py-3 hover:bg-blue-50 rounded-xl transition-colors group border border-transparent hover:border-blue-100 flex flex-col items-start"
                            :class="selectedValue == item.id ? 'bg-blue-50 border-blue-200' : ''"
                    >
                        <div class="font-medium text-slate-800 group-hover:text-blue-700" x-text="item.label"></div>
                        <div class="text-xs text-slate-400 group-hover:text-blue-500" x-text="item.sub"></div>
                    </button>
                </template>
                <div x-show="filteredItems.length === 0" class="p-8 text-center text-slate-500 text-sm">
                    No se encontraron resultados
                </div>
            </div>
        </div>
    </div>
</div>
