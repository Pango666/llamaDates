@extends('layouts.app')
@section('title','Nuevo Movimiento de Inventario')

@section('header-actions')
  <a href="{{ route('admin.inv.movs.index') }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Movimientos
  </a>
@endsection

@section('content')
  <div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nuevo Movimiento
        </h1>
        <p class="text-sm text-slate-600 mt-1">
          Registre entradas (compras/ajustes) o salidas (ventas/uso) de inv.
        </p>
      </div>
    </div>

    <form method="post" action="{{ route('admin.inv.movs.store') }}" class="card space-y-6" id="movForm">
      @csrf

      {{-- PASO 1: TIPO DE MOVIMIENTO --}}
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">1. Tipo de Movimiento</label>
        <div class="grid grid-cols-2 gap-4">
            <label class="cursor-pointer relative">
                <input type="radio" name="type" value="in" class="peer sr-only" checked>
                <div class="p-4 border border-slate-200 rounded-xl bg-slate-50 peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:ring-1 peer-checked:ring-emerald-500 transition-all flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 peer-checked:text-emerald-600 peer-checked:border-emerald-200">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                    </div>
                    <div>
                        <div class="font-semibold text-slate-900 peer-checked:text-emerald-800">Entrada</div>
                        <div class="text-xs text-slate-500 peer-checked:text-emerald-600">Compras, devoluciones, ajuste positivo</div>
                    </div>
                </div>
            </label>

            <label class="cursor-pointer relative">
                <input type="radio" name="type" value="out" class="peer sr-only">
                <div class="p-4 border border-slate-200 rounded-xl bg-slate-50 peer-checked:bg-amber-50 peer-checked:border-amber-500 peer-checked:ring-1 peer-checked:ring-amber-500 transition-all flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 peer-checked:text-amber-600 peer-checked:border-amber-200">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>
                    </div>
                    <div>
                        <div class="font-semibold text-slate-900 peer-checked:text-amber-800">Salida</div>
                        <div class="text-xs text-slate-500 peer-checked:text-amber-600">Uso interno, ventas, ajuste negativo</div>
                    </div>
                </div>
            </label>
        </div>
      </div>

      {{-- PASO 2: PRODUCTO (MODAL) --}}
      <div class="grid md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700">2. Producto <span class="text-red-500">*</span></label>
            <button type="button" id="btnProduct" class="w-full text-left border border-slate-300 rounded-xl px-3 py-3 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 transition-colors group">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium text-slate-700 group-hover:text-slate-900" id="productLabel">Seleccionar producto...</div>
                        <div class="text-xs text-slate-400 group-hover:text-slate-500" id="productSubLabel">Click para buscar</div>
                    </div>
                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </button>
            <input type="hidden" name="product_id" id="product_id" required>
            @error('product_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            
            <div id="stockHint" class="hidden mt-2 p-2 bg-slate-100 rounded-lg text-xs text-slate-600 flex items-center gap-2">
                <span class="font-semibold">Stock actual:</span> <span id="currentStockVal">--</span>
            </div>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700">Ubicación <span class="text-red-500">*</span></label>
            <select name="location_id" class="w-full border border-slate-300 rounded-xl px-3 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500/20" required>
                @foreach($locations as $loc)
                  <option value="{{ $loc->id }}" @selected(old('location_id') == $loc->id)>{{ $loc->name }}</option>
                @endforeach
            </select>
          </div>
      </div>

      {{-- PASO 3: DETALLES --}}
      <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-4">
        <label class="block text-sm font-medium text-slate-700">3. Detalles del movimiento</label>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="col-span-2 md:col-span-1">
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1 block">Cantidad</label>
                <input type="number" name="qty" min="1" value="1" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500/20 md:text-lg font-semibold text-slate-800" required>
            </div>
            
            <div class="col-span-2 md:col-span-1" id="costField">
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1 block">Costo Unit. (Bs)</label>
                <input type="number" name="unit_cost" step="0.01" min="0" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20" placeholder="0.00">
            </div>

            <div class="col-span-2 md:col-span-1">
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1 block">Lote <span class="text-red-500">*</span></label>
                
                {{-- Input texto para Entrada --}}
                <input type="text" id="lotInput" name="lot_in" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20" placeholder="Código de lote">
                
                {{-- Helper para Entrada: Lote existente --}}
                <select id="lotHelper" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm mt-2 text-slate-600 bg-slate-50 focus:ring-2 focus:ring-blue-500/20 hidden">
                    <option value="">(Opcional) Seleccionar lote existente...</option>
                </select>

                {{-- Select para Salida (oculto por defecto) --}}
                <select id="lotSelect" name="lot_out" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 hidden" disabled>
                    <option value="">Seleccione lote...</option>
                </select>
                <div id="lotLoading" class="hidden text-xs text-blue-500 mt-1">Cargando lotes...</div>
            </div>

            <div class="col-span-2 md:col-span-1" id="expiryContainer">
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1 block">Vencimiento <span class="text-red-500">*</span></label>
                <input type="date" name="expires_at" id="expiresInput" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
            <div id="invoiceField">
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1 block">N° Recibo / Doc</label>
                <input type="text" name="purchase_invoice_number" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20" placeholder="Ej: FAC-12345">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1 block">Notas</label>
                <input type="text" name="note" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20" placeholder="Observaciones...">
            </div>
        </div>
      </div>

      <div class="pt-4 flex justify-end gap-3">
         <a href="{{ route('admin.inv.movs.index') }}" class="btn btn-ghost">Cancelar</a>
         <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-500/30 px-6">Guardar Movimiento</button>
      </div>
    </form>
  </div>

  {{-- MODAL PICKER --}}
  <div id="pickerBackdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden z-50 transition-opacity"></div>
  <div id="pickerModal" class="fixed inset-0 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all scale-100 opacity-100 flex flex-col max-h-[80vh]">
      <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 flex-shrink-0">
        <div>
          <div class="font-bold text-slate-900 text-lg">Seleccionar Producto</div>
          <div class="text-xs text-slate-500" id="pickerSubtitle">Mostrando todos los productos</div>
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
                 placeholder="Buscar por nombre, código..." autocomplete="off">
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
    
    // --- STATE ---
    let currentType = 'in'; // 'in' | 'out'
    let allProducts = []; // Cache simple
    
    // --- DOM Elements ---
    const typesRadios = document.getElementsByName('type');
    const btnProduct  = document.getElementById('btnProduct');
    const productId   = document.getElementById('product_id');
    const productLabel = document.getElementById('productLabel');
    const productSub   = document.getElementById('productSubLabel');
    const costField    = document.getElementById('costField');
    const stockHint    = document.getElementById('stockHint');
    const currentStockVal = document.getElementById('currentStockVal');

    // Lot & Expiry & Invoice
    const lotInput = document.getElementById('lotInput');
    const lotSelect = document.getElementById('lotSelect');
    const lotHelper = document.getElementById('lotHelper'); // New
    const lotLoading = document.getElementById('lotLoading');
    
    const expiryContainer = document.getElementById('expiryContainer');
    const expiresInput = document.getElementById('expiresInput');
    
    const invoiceField = document.getElementById('invoiceField');

    // Modal
    const backdrop   = document.getElementById('pickerBackdrop');
    const modal      = document.getElementById('pickerModal');
    const closeBtn   = document.getElementById('pickerClose');
    const searchEl   = document.getElementById('pickerSearch');
    const listEl     = document.getElementById('pickerList');
    const subTitleEl = document.getElementById('pickerSubtitle');
    
    // --- Logic ---
    
    // 1. Manejo de Tipo (Entrada/Salida)
    typesRadios.forEach(r => {
        r.addEventListener('change', (e) => {
            currentType = e.target.value;
            handleTypeChange();
        });
    });

    function handleTypeChange() {
        resetProductSelection();
        allProducts = []; 
        applyTypeUi();
    }
    
    function applyTypeUi() {
        if(currentType === 'out') {
            costField.style.opacity = '0.5';
            costField.querySelector('input').disabled = true;
            invoiceField.style.opacity = '0.5';
            
            lotInput.classList.add('hidden');
            lotInput.disabled = true;
            
            lotHelper.classList.add('hidden'); // Hide helper
            
            lotSelect.classList.remove('hidden');
            lotSelect.disabled = false;
            
            expiryContainer.style.opacity = '0';
            expiryContainer.style.pointerEvents = 'none';
            expiresInput.disabled = true;
            
            subTitleEl.textContent = 'Mostrando solo productos con STOCK DISPONIBLE';
            document.getElementById('reqLot').classList.remove('hidden'); 
            document.getElementById('reqExp').classList.add('hidden');
        } else {
            costField.style.opacity = '1';
            costField.querySelector('input').disabled = false;
            invoiceField.style.opacity = '1';
            
            lotInput.classList.remove('hidden');
            lotInput.disabled = false;
            
            lotHelper.classList.remove('hidden'); // Show helper
            
            lotSelect.classList.add('hidden');
            lotSelect.disabled = true;
            
            expiryContainer.style.opacity = '1';
            expiryContainer.style.pointerEvents = 'auto';
            expiresInput.disabled = false;

            subTitleEl.textContent = 'Mostrando todos los productos';
            document.getElementById('reqLot').classList.remove('hidden');
            document.getElementById('reqExp').classList.remove('hidden');
        }
    }
    
    function resetProductSelection() {
        productId.value = '';
        productLabel.textContent = 'Seleccionar producto...';
        productLabel.classList.remove('text-slate-900', 'font-bold');
        productSub.textContent = 'Click para buscar';
        stockHint.classList.add('hidden');
        
        lotSelect.innerHTML = '<option value="">Seleccione lote...</option>';
        lotHelper.innerHTML = '<option value="">(Opcional) Seleccionar lote existente...</option>';
        lotInput.value = '';
    }

    async function fetchProducts() {
        listEl.innerHTML = '<div class="p-8 text-center text-slate-400"><svg class="w-8 h-8 animate-spin mx-auto mb-2" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>Cargando catálogo...</div>';
        
        try {
            const hasStock = currentType === 'out' ? '1' : '0';
            const res = await fetch(`{{ route('admin.inv.movs.products_options') }}?has_stock=${hasStock}`);
            if(!res.ok) throw new Error('Network error');
            const data = await res.json();
            allProducts = data;
            renderList('');
        } catch(e) {
            console.error(e);
            listEl.innerHTML = '<div class="p-4 text-center text-red-500 text-sm">Error al cargar productos. Intente recargar la página.</div>';
        }
    }

    function renderList(filterText = '') {
        const q = filterText.toLowerCase().trim();
        const filtered = allProducts.filter(p => {
             const name   = (p.name || '').toLowerCase();
             const code   = (p.sku || '').toLowerCase();
             return name.includes(q) || code.includes(q); 
        });

        listEl.innerHTML = '';
        
        if(filtered.length === 0) {
            listEl.innerHTML = '<div class="p-8 text-center text-slate-500">No se encontraron productos.</div>';
            return;
        }

        filtered.slice(0, 50).forEach(p => {
            const btn = document.createElement('button');
            btn.type  = 'button';
            btn.className = 'w-full text-left px-4 py-3 hover:bg-blue-50 rounded-xl transition-all group border border-transparent hover:border-blue-100 flex items-center justify-between';
            
            let items = `Stock: ${p.stock || 0} ${p.unit || 'u.'}`;
            let stockClass = (p.stock > 0) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500';
            if(p.stock <= 5 && p.stock > 0) stockClass = 'bg-amber-100 text-amber-700';

            btn.innerHTML = `
                <div>
                    <div class="font-medium text-slate-800 group-hover:text-blue-700 text-sm md:text-base">${p.name}</div>
                    <div class="text-xs text-slate-400 group-hover:text-blue-500">${p.sku ? p.sku + ' - ' : ''} ${p.unit || 'Unidad'}</div>
                </div>
                <div class="px-2 py-1 rounded-md text-xs font-bold ${stockClass}">
                    ${items}
                </div>
            `;
            
            btn.onclick = () => selectProduct(p);
            listEl.appendChild(btn);
        });
    }

    async function selectProduct(p) {
        productId.value = p.id;
        
        productLabel.textContent = p.name;
        productLabel.classList.add('text-slate-900', 'font-bold');
        productSub.textContent   = `Código: ${p.sku || 'S/N'} | Disp: ${p.stock}`;
        
        currentStockVal.textContent = `${p.stock} ${p.unit || ''}`;
        stockHint.classList.remove('hidden');
        
        if (p.stock <= 0) {
            stockHint.className = 'mt-2 p-2 bg-red-50 rounded-lg text-xs text-red-600 flex items-center gap-2 border border-red-100';
        } else {
            stockHint.className = 'mt-2 p-2 bg-emerald-50 rounded-lg text-xs text-emerald-600 flex items-center gap-2 border border-emerald-100';
        }
        
        // Cargar lotes SIEMPRE (para llenar Select o Helper)
        await fetchLots(p.id);

        closeModal();
    }
    
    async function fetchLots(pId) {
        lotSelect.innerHTML = '<option>Cargando lotes...</option>';
        lotHelper.innerHTML = '<option>Cargando...</option>';
        lotSelect.disabled = true;
        lotHelper.disabled = true;
        lotLoading.classList.remove('hidden');
        
        try {
            const res = await fetch(`{{ route('admin.inv.movs.lots_options') }}?product_id=${pId}`);
            const lots = await res.json();
            
            lotSelect.innerHTML = '<option value="">Seleccione lote...</option>';
            lotHelper.innerHTML = '<option value="">(Opcional) Seleccionar lote existente...</option>';
            
            if(lots.length === 0) {
                 lotSelect.innerHTML += '<option value="" disabled>Sin lotes disponibles</option>';
                 lotHelper.innerHTML = '<option value="">Sin lotes previos</option>';
                 lotHelper.disabled = true;
            } else {
                 lots.forEach(l => {
                     let dateStr = l.expires_at;
                     if(dateStr && dateStr.includes('T')) dateStr = dateStr.split('T')[0];
                     
                     const exp = dateStr ? ` (Vence: ${dateStr})` : '';
                     const txt = `${l.lot} - Disp: ${l.balance}${exp}`;
                     
                     // Option for OUT
                     const opt = document.createElement('option');
                     opt.value = l.lot;
                     opt.textContent = txt;
                     lotSelect.appendChild(opt);

                     // Option for IN Helper
                     const opt2 = document.createElement('option');
                     opt2.value = l.lot;
                     opt2.textContent = txt;
                     if(dateStr) opt2.dataset.expires = dateStr;
                     lotHelper.appendChild(opt2);
                 });
                 lotHelper.disabled = false;
            }
        } catch(e) {
            console.error(e);
            lotSelect.innerHTML = '<option>Error al cargar lotes</option>';
        } finally {
            // Solo habilitar el select de salida si estamos en OUT
            if(currentType === 'out') {
                lotSelect.disabled = false;
            } else {
                lotSelect.disabled = true;
            }

            // helper check
            if(lotHelper.options.length > 1) lotHelper.disabled = false; 

            lotLoading.classList.add('hidden');
        }
    }

    // Event Helper Change
    lotHelper.addEventListener('change', function() {
        const val = this.value;
        if(val) {
            lotInput.value = val;
            // Auto fill expiry
            const opt = this.options[this.selectedIndex];
            if(opt.dataset.expires) {
                expiresInput.value = opt.dataset.expires;
            }
        }
    });

    // 5. Modal actions
    function openModal() {
        modal.classList.remove('hidden');
        backdrop.classList.remove('hidden');
        searchEl.value = '';
        setTimeout(() => searchEl.focus(), 50);
        
        if(allProducts.length === 0) {
            fetchProducts();
        } else {
            renderList('');
        }
    }

    function closeModal() {
        modal.classList.add('hidden');
        backdrop.classList.add('hidden');
    }

    // Bindings
    btnProduct.onclick = openModal;
    closeBtn.onclick   = closeModal;
    backdrop.onclick   = closeModal;
    searchEl.oninput   = (e) => renderList(e.target.value);
    
    document.addEventListener('keydown', (e) => {
        if(e.key === 'Escape') closeModal();
    });
    
    // Init state
    applyTypeUi();
});
</script>
@endsection
