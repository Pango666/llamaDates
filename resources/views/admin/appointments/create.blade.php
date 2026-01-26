@extends('layouts.app')
@section('title','Nueva Cita')

@section('header-actions')
  @can('appointments.view')
    <a href="{{ route('admin.appointments.index') }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
      </svg>
      Volver al listado
    </a>
  @endcan
@endsection


@section('content')
  <div class="max-w-4xl mx-auto">
    <div class="card">
      <div class="border-b border-slate-200 pb-4 mb-6">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          Programar Nueva Cita
        </h1>
        <p class="text-sm text-slate-600 mt-1">Complete la información requerida para agendar una nueva cita.</p>
      </div>

      @php
        $selectedPatientId  = old('patient_id', $prefill['patient_id']);
        $selectedServiceId  = old('service_id', $prefill['service_id']);
        $selectedDentistId  = old('dentist_id', $prefill['dentist_id']);

        $selectedPatientText = 'Seleccionar un paciente...';
        $selectedServiceText = 'Seleccionar un servicio...';
        $selectedDentistText = 'Seleccionar un odontólogo...';

        foreach($patients as $p) {
            if ($selectedPatientId == $p->id) $selectedPatientText = $p->last_name . ', ' . $p->first_name;
        }
        foreach($services as $s) {
            if ($selectedServiceId == $s->id) $selectedServiceText = $s->name;
        }
        foreach($dentists as $d) {
            if ($selectedDentistId == $d->id) $selectedDentistText = $d->name;
        }

        // Prepare JSON data for JS
        $patientsJson = $patients->map(fn($p) => [
            'id' => (string)$p->id,
            'label' => $p->last_name . ', ' . $p->first_name . ($p->phone ? " ({$p->phone})" : ''),
            'sub'  => $p->email
        ])->values();

        $servicesJson = $services->map(fn($s) => [
            'id' => (string)$s->id,
            'label' => $s->name,
            'sub' => $s->duration_min . ' min'
        ])->values();

        $dentistsJson = $dentists->map(fn($d) => [
            'id' => (string)$d->id,
            'label' => $d->name,
            'sub' => $d->specialty
        ])->values();
      @endphp

      <form method="post" action="{{ route('admin.appointments.store') }}" id="formAppt">
        @csrf

        <div class="grid gap-6 md:grid-cols-2">
          {{-- Paciente --}}
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Paciente <span class="text-red-500">*</span>
            </label>
            <button type="button" id="btnPatient"
                    class="w-full text-left border border-slate-300 rounded-xl px-3 py-2.5 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 transition-colors group">
              <div class="text-sm font-medium text-slate-700 group-hover:text-slate-900" id="patientLabel">{{ $selectedPatientText }}</div>
              <div class="text-xs text-slate-400 group-hover:text-slate-500">Escribe para filtrar</div>
            </button>
            <input type="hidden" name="patient_id" id="patient_id" value="{{ $selectedPatientId }}">
            @error('patient_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Servicio --}}
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
              Servicio <span class="text-red-500">*</span>
            </label>
            <button type="button" id="btnService"
                    class="w-full text-left border border-slate-300 rounded-xl px-3 py-2.5 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 transition-colors group">
              <div class="text-sm font-medium text-slate-700 group-hover:text-slate-900" id="serviceLabel">{{ $selectedServiceText }}</div>
              <div class="text-xs text-slate-400 group-hover:text-slate-500">Escribe para filtrar</div>
            </button>
            <input type="hidden" name="service_id" id="service_id" value="{{ $selectedServiceId }}">
            @error('service_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Odontólogo --}}
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Odontólogo <span class="text-red-500">*</span>
            </label>
            <button type="button" id="btnDentist"
                    class="w-full text-left border border-slate-300 rounded-xl px-3 py-2.5 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200 transition-colors group">
              <div class="text-sm font-medium text-slate-700 group-hover:text-slate-900" id="dentistLabel">{{ $selectedDentistText }}</div>
              <div class="text-xs text-slate-400 group-hover:text-slate-500">Escribe para filtrar</div>
            </button>
            <input type="hidden" name="dentist_id" id="dentist_id" value="{{ $selectedDentistId }}">
            @error('dentist_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Fecha --}}
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              Fecha <span class="text-red-500">*</span>
            </label>
            <input
              type="date"
              id="date"
              name="date"
              value="{{ old('date', $prefill['date'] ?? now()->toDateString()) }}"
              class="w-full border border-slate-300 rounded-xl px-3 py-2.5 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              required
              min="{{ now()->toDateString() }}"
            >
            @error('date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Horarios --}}
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Horario <span class="text-red-500">*</span>
            </label>
            <div id="slots" class="grid grid-cols-2 lg:grid-cols-4 gap-2 mt-2">
                <div class="text-sm text-slate-500 col-span-full border border-slate-200 rounded-xl p-4 text-center bg-slate-50">
                    Seleccione servicio, odontólogo y fecha para ver horarios
                </div>
            </div>
            <input type="hidden" name="start_time" id="start_time_input" required>
            <div id="slotsHint" class="text-slate-500 text-xs flex items-center gap-1 mt-2">
            </div>
          </div>

          {{-- Notas --}}
          <div class="md:col-span-2 space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Notas adicionales <span class="text-slate-400 font-normal text-xs">(Opcional)</span>
            </label>
            <textarea name="notes" rows="3" class="w-full border border-slate-300 rounded-xl px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Observaciones, comentarios...">{{ old('notes', $prefill['notes']) }}</textarea>
          </div>
        </div>

        {{-- Acciones --}}
        <div class="flex gap-3 pt-6 mt-6 border-t border-slate-200">
          @can('appointments.create')
            <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 shadow-lg shadow-blue-500/30">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              Programar Cita
            </button>
          @endcan

          @can('appointments.view')
            <a href="{{ route('admin.appointments.index') }}" class="btn bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors">
              Cancelar
            </a>
          @endcan
        </div>
      </form>
    </div>
  </div>

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
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  // ELEMENTOS DOM
  const btnPatient = document.getElementById('btnPatient');
  const btnService = document.getElementById('btnService');
  const btnDentist = document.getElementById('btnDentist');

  const patientId = document.getElementById('patient_id');
  const serviceId = document.getElementById('service_id');
  const dentistId = document.getElementById('dentist_id');

  const patientLabel = document.getElementById('patientLabel');
  const serviceLabel = document.getElementById('serviceLabel');
  const dentistLabel = document.getElementById('dentistLabel');

  const dateInput = document.getElementById('date');
  const slotsSelect = document.getElementById('slots');
  const slotsHint = document.getElementById('slotsHint');

  // MODAL
  const backdrop = document.getElementById('pickerBackdrop');
  const modal    = document.getElementById('pickerModal');
  const closeBtn = document.getElementById('pickerClose');
  const titleEl  = document.getElementById('pickerTitle');
  const searchEl = document.getElementById('pickerSearch');
  const listEl   = document.getElementById('pickerList');

  // DATA
  const PATIENTS = @json($patientsJson);
  const SERVICES = @json($servicesJson);
  const DENTISTS = @json($dentistsJson);

  let currentType = null; // 'patient'|'service'|'dentist'

  // FUNCIONES PICKER
  function openPicker(type) {
    currentType = type;
    searchEl.value = '';
    
    // Configurar textos
    if(type==='patient') titleEl.textContent = 'Seleccionar Paciente';
    if(type==='service') titleEl.textContent = 'Seleccionar Servicio';
    if(type==='dentist') titleEl.textContent = 'Seleccionar Odontólogo';

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
    if(currentType==='patient') items = PATIENTS;
    if(currentType==='service') items = SERVICES;
    if(currentType==='dentist') items = DENTISTS;

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
    if(currentType==='patient') {
        patientId.value = item.id;
        patientLabel.textContent = item.label;
        patientLabel.classList.add('text-slate-900');
    }
    if(currentType==='service') {
        serviceId.value = item.id;
        serviceLabel.textContent = item.label;
        serviceLabel.classList.add('text-slate-900');
        loadSlots(); // Recargar al cambiar
    }
    if(currentType==='dentist') {
        dentistId.value = item.id;
        dentistLabel.textContent = item.label;
        dentistLabel.classList.add('text-slate-900');
        loadSlots(); // Recargar al cambiar
    }
    closePicker();
  }

  // EVENTOS
  btnPatient.onclick = () => openPicker('patient');
  btnService.onclick = () => openPicker('service');
  btnDentist.onclick = () => openPicker('dentist');
  
  closeBtn.onclick = closePicker;
  backdrop.onclick = closePicker;
  searchEl.oninput = renderList;

  // LÓGICA DE HORARIOS
  const hiddenStartInput = document.getElementById('start_time_input');
  
  function renderSlots(slots) {
     slotsSelect.innerHTML = ''; // ahora slotsSelect es el div grid
     hiddenStartInput.value = '';
     
     if(slots.length === 0) {
         slotsSelect.innerHTML = `<div class="text-sm text-slate-500 col-span-full border border-slate-200 rounded-xl p-4 text-center bg-slate-50">No hay horarios disponibles para esta selección.</div>`;
         return;
     }

     slots.forEach(time => {
         const tShort = time.substring(0,5);
         
         const btn = document.createElement('button');
         btn.type = 'button';
         btn.className = 'flex flex-col items-center justify-center p-3 border border-slate-200 rounded-xl bg-white hover:bg-blue-50 hover:border-blue-200 transition-all focus:outline-none focus:ring-2 focus:ring-blue-500/50';
         
         btn.innerHTML = `
             <span class="font-semibold text-slate-700">${tShort}</span>
         `;
         
         btn.onclick = () => {
             // Desmarcar todos
             slotsSelect.querySelectorAll('button').forEach(b => {
                 b.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50', 'border-blue-500');
                 b.classList.add('border-slate-200', 'bg-white');
             });
             
             // Marcar actual
             btn.classList.remove('border-slate-200', 'bg-white');
             btn.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50', 'border-blue-500');
             
             hiddenStartInput.value = time.length === 5 ? time+':00' : time;
         };
         
         slotsSelect.appendChild(btn);
     });
  }

  async function loadSlots() {
    const sId = serviceId.value;
    const dId = dentistId.value;
    const date = dateInput.value;

    if(!sId || !dId || !date) {
        slotsSelect.innerHTML = `<div class="text-sm text-slate-500 col-span-full border border-slate-200 rounded-xl p-4 text-center bg-slate-50">Seleccione servicio, odontólogo y fecha.</div>`;
        return;
    }

    // Loading state
    slotsSelect.innerHTML = `<div class="text-sm text-slate-500 col-span-full border border-slate-200 rounded-xl p-4 text-center bg-slate-50 flex items-center justify-center gap-2"><svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg> Buscando disponibilidad...</div>`;

    try {
        const url = `{{ route('admin.appointments.availability') }}?dentist_id=${dId}&service_id=${sId}&date=${date}`;
        const res = await fetch(url);
        const slots = await res.json();
        
        renderSlots(slots);
        
        if(slots.length > 0) {
           slotsHint.className = 'text-green-600 text-xs mt-2 flex items-center gap-1';
           slotsHint.innerHTML = `<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> ${slots.length} horarios encontrados.`;
        } else {
           slotsHint.className = 'text-amber-600 text-xs mt-2';
           slotsHint.innerHTML = 'Sin resultados.';
        }

    } catch(e) {
        console.error(e);
        slotsSelect.innerHTML = `<div class="text-sm text-red-500 col-span-full border border-red-200 rounded-xl p-4 text-center bg-red-50">Error al cargar horarios.</div>`;
    }
  }

  dateInput.onchange = loadSlots;
  
  // Init si ya hay valores (old input)
  if(serviceId.value && dentistId.value && dateInput.value) {
      loadSlots();
  }
});
</script>
@endsection
