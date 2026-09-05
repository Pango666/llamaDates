@extends('layouts.app')
@section('title','Nuevo plan de tratamiento')

@section('header-actions')
  <a href="{{ route('admin.patients.plans.index',$patient) }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver
</a>
@endsection

@section('content')
  <form method="post" action="{{ route('admin.patients.plans.store',$patient) }}" class="card max-w-2xl mx-auto">
    @csrf
    <h3 class="font-semibold mb-4 text-lg border-b pb-2">Nuevo plan para {{ $patient->last_name }}, {{ $patient->first_name }}</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
      @php
        $selectedServiceId  = old('service_id');
        $selectedDentistId  = old('dentist_id');

        $selectedServiceText = 'Seleccione un servicio';
        $selectedDentistText = 'Seleccione un doctor';
        $selectedServicePrice = '';

        foreach($services as $s) {
            if ($selectedServiceId == $s->id) {
                $selectedServiceText = $s->name . ' (Bs ' . number_format($s->price, 2) . ')';
                $selectedServicePrice = $s->price;
            }
        }
        foreach($dentists as $d) {
            if ($selectedDentistId == $d->id) {
                $selectedDentistText = $d->user->name ?? 'Dr. '.$d->id;
            }
        }

        $servicesJson = $services->map(fn($s) => [
            'id' => (string)$s->id,
            'label' => $s->name . ' (Bs ' . number_format($s->price, 2) . ')',
            'sub' => $s->duration_min . ' min',
            'price' => $s->price
        ])->values();

        $dentistsJson = $dentists->map(fn($d) => [
            'id' => (string)$d->id,
            'label' => $d->user->name ?? 'Dr. '.$d->id,
            'sub' => $d->specialty
        ])->values();
      @endphp

      <div>
        <label class="block text-sm font-medium mb-1 text-slate-700">Servicio / Tratamiento *</label>
        <button type="button" id="btnService"
                class="w-full text-left border border-slate-300 rounded-xl px-3 py-2.5 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 transition-colors group">
          <div class="text-sm font-medium text-slate-700 group-hover:text-slate-900" id="serviceLabel">{{ $selectedServiceText }}</div>
          <div class="text-xs text-slate-400 group-hover:text-slate-500">Escribe para filtrar</div>
        </button>
        <input type="hidden" name="service_id" id="service_id" value="{{ $selectedServiceId }}">
        @error('service_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium mb-1 text-slate-700">Odontólogo Asignado *</label>
        <button type="button" id="btnDentist"
                class="w-full text-left border border-slate-300 rounded-xl px-3 py-2.5 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 transition-colors group">
          <div class="text-sm font-medium text-slate-700 group-hover:text-slate-900" id="dentistLabel">{{ $selectedDentistText }}</div>
          <div class="text-xs text-slate-400 group-hover:text-slate-500">Escribe para filtrar</div>
        </button>
        <input type="hidden" name="dentist_id" id="dentist_id" value="{{ $selectedDentistId }}">
        @error('dentist_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium mb-1 text-slate-700">Costo Total Estimado (Bs) *</label>
        <input type="number" step="0.01" name="estimate_total" id="estimate_total" value="{{ old('estimate_total', $selectedServicePrice) }}" class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 outline-none" required>
        @error('estimate_total') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium mb-1 text-slate-700">Número de Sesiones Estimadas *</label>
        <input type="number" min="1" name="total_sessions" value="{{ old('total_sessions') }}" class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 outline-none" placeholder="Ej. 3" required>
        @error('total_sessions') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
      </div>
      
      <div>
        <label class="block text-sm font-medium mb-1 text-slate-700">Código de Diente (Opcional)</label>
        <input type="text" name="tooth_code" value="{{ old('tooth_code') }}" class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 outline-none" placeholder="Ej. 18">
      </div>
      
      <div>
        <label class="block text-sm font-medium mb-1 text-slate-700">Superficie (Opcional)</label>
        <select name="surface" class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 outline-none">
          <option value="">N/A</option>
          <option value="O" {{ old('surface') == 'O' ? 'selected' : '' }}>Oclusal (O)</option>
          <option value="M" {{ old('surface') == 'M' ? 'selected' : '' }}>Mesial (M)</option>
          <option value="D" {{ old('surface') == 'D' ? 'selected' : '' }}>Distal (D)</option>
          <option value="B" {{ old('surface') == 'B' ? 'selected' : '' }}>Bucal/Vestibular (B)</option>
          <option value="L" {{ old('surface') == 'L' ? 'selected' : '' }}>Lingual/Palatino (L)</option>
          <option value="I" {{ old('surface') == 'I' ? 'selected' : '' }}>Incisal (I)</option>
        </select>
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1 text-slate-700">Título Personalizado (Opcional)</label>
        <input name="title" value="{{ old('title') }}" class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 outline-none" placeholder="Si se deja vacío, se usará el nombre del servicio">
      </div>
    </div>

    <div class="mt-6 flex justify-end">
      <button class="btn btn-primary px-6">Crear Plan de Tratamiento</button>
    </div>
  </form>

  {{-- MODAL PICKER --}}
  <div id="pickerBackdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden z-50 transition-opacity"></div>
  <div id="pickerModal" class="fixed inset-0 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all scale-100 opacity-100">
      <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <div>
          <div class="font-semibold text-slate-900" id="pickerTitle">Seleccionar</div>
          <div class="text-xs text-slate-500" id="pickerSubtitle">Escribe para filtrar opciones</div>
        </div>
        <button type="button" id="pickerClose" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <div class="p-4 space-y-3">
        <div class="relative">
          <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input id="pickerSearch" type="text"
                 class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-400"
                 placeholder="Buscar..." autocomplete="off">
        </div>

        <div id="pickerList" class="max-h-[60vh] overflow-y-auto overflow-x-hidden space-y-1 pr-1 custom-scrollbar"></div>
      </div>
    </div>
  </div>

  <style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const btnService = document.getElementById('btnService');
      const btnDentist = document.getElementById('btnDentist');

      const serviceId = document.getElementById('service_id');
      const dentistId = document.getElementById('dentist_id');

      const serviceLabel = document.getElementById('serviceLabel');
      const dentistLabel = document.getElementById('dentistLabel');
      
      const estimateTotal = document.getElementById('estimate_total');

      const backdrop = document.getElementById('pickerBackdrop');
      const modal    = document.getElementById('pickerModal');
      const closeBtn = document.getElementById('pickerClose');
      const titleEl  = document.getElementById('pickerTitle');
      const searchEl = document.getElementById('pickerSearch');
      const listEl   = document.getElementById('pickerList');

      const SERVICES = @json($servicesJson);
      const DENTISTS = @json($dentistsJson);

      let currentType = null;

      function openPicker(type) {
        currentType = type;
        searchEl.value = '';
        
        if(type==='service') titleEl.textContent = 'Seleccionar Servicio';
        if(type==='dentist') titleEl.textContent = 'Seleccionar Odontólogo';

        modal.classList.remove('hidden');
        backdrop.classList.remove('hidden');
        
        setTimeout(() => searchEl.focus(), 50);
        renderList();
      }

      function closePicker() {
        modal.classList.add('hidden');
        backdrop.classList.add('hidden');
        currentType = null;
      }

      function renderList() {
        let items = currentType === 'service' ? SERVICES : DENTISTS;
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
        if(currentType==='service') {
            serviceId.value = item.id;
            serviceLabel.textContent = item.label;
            serviceLabel.classList.add('text-slate-900');
            estimateTotal.value = item.price;
        }
        if(currentType==='dentist') {
            dentistId.value = item.id;
            dentistLabel.textContent = item.label;
            dentistLabel.classList.add('text-slate-900');
        }
        closePicker();
      }

      if (btnService) btnService.onclick = () => openPicker('service');
      if (btnDentist) btnDentist.onclick = () => openPicker('dentist');
      
      closeBtn.onclick = closePicker;
      backdrop.onclick = closePicker;
      searchEl.oninput = renderList;
    });
  </script>
@endsection
