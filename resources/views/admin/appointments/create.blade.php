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
        <p class="text-sm text-slate-600 mt-1">Complete la información requerida para agendar una nueva cita</p>
      </div>

      @php
        $selectedPatientId  = old('patient_id', $prefill['patient_id']);
        $selectedServiceId  = old('service_id', $prefill['service_id']);
        $selectedDentistId  = old('dentist_id', $prefill['dentist_id']);

        $selectedPatientText = '';
        $selectedServiceText = '';
        $selectedDentistText = '';
      @endphp

      {{-- Pre-calcular textos seleccionados --}}
      @foreach($patients as $p)
        @php
          if ($selectedPatientId == $p->id) {
            $selectedPatientText = $p->last_name . ', ' . $p->first_name;
          }
        @endphp
      @endforeach

      @foreach($services as $s)
        @php
          if ($selectedServiceId == $s->id) {
            $selectedServiceText = $s->name;
          }
        @endphp
      @endforeach

      @foreach($dentists as $d)
        @php
          if ($selectedDentistId == $d->id) {
            $selectedDentistText = $d->name;
          }
        @endphp
      @endforeach

      <form method="post" action="{{ route('admin.appointments.store') }}" id="formAppt">
        @csrf

        <div class="grid gap-6 md:grid-cols-2">
          {{-- Paciente (input + datalist + hidden) --}}
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Paciente
              <span class="text-red-500">*</span>
            </label>

            <input
              type="text"
              id="patient_input"
              list="patientsList"
              class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              placeholder="Escribe el nombre del paciente..."
              value="{{ $selectedPatientText }}"
              autocomplete="off"
            >

            <datalist id="patientsList">
              @foreach($patients as $p)
                <option
                  value="{{ $p->last_name }}, {{ $p->first_name }}"
                  data-id="{{ $p->id }}"
                ></option>
              @endforeach
            </datalist>

            {{-- el que realmente se envía --}}
            <input type="hidden" name="patient_id" id="patient_id" value="{{ $selectedPatientId }}">

            @error('patient_id')
              <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Servicio (input + datalist + hidden) --}}
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
              Servicio
              <span class="text-red-500">*</span>
            </label>

            <input
              type="text"
              id="service_input"
              list="servicesList"
              class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              placeholder="Escribe el servicio..."
              value="{{ $selectedServiceText }}"
              autocomplete="off"
            >

            <datalist id="servicesList">
              @foreach($services as $s)
                <option
                  value="{{ $s->name }}"
                  data-id="{{ $s->id }}"
                ></option>
              @endforeach
            </datalist>

            <input type="hidden" name="service_id" id="service_id" value="{{ $selectedServiceId }}">

            @error('service_id')
              <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Odontólogo (input + datalist + hidden) --}}
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Odontólogo
              <span class="text-red-500">*</span>
            </label>

            <input
              type="text"
              id="dentist_input"
              list="dentistsList"
              class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              placeholder="Escribe el nombre del odontólogo..."
              value="{{ $selectedDentistText }}"
              autocomplete="off"
            >

            <datalist id="dentistsList">
              @foreach($dentists as $d)
                <option
                  value="{{ $d->name }}"
                  data-id="{{ $d->id }}"
                ></option>
              @endforeach
            </datalist>

            <input type="hidden" name="dentist_id" id="dentist_id" value="{{ $selectedDentistId }}">

            @error('dentist_id')
              <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Fecha --}}
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              Fecha
              <span class="text-red-500">*</span>
            </label>
            <input
              type="date"
              id="date"
              name="date"
              value="{{ old('date', $prefill['date'] ?? now()->toDateString()) }}"
              class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              required
              min="{{ now()->toDateString() }}"
            >
            @error('date')
              <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Horarios --}}
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Horario
              <span class="text-red-500">*</span>
            </label>
            <select name="start_time" id="slots" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required disabled>
              <option value="">— Primero seleccione los datos anteriores —</option>
            </select>
            <div id="slotsHint" class="text-slate-500 text-xs flex items-center gap-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Seleccione servicio, odontólogo y fecha para ver horarios disponibles
            </div>
            @error('start_time')
              <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Notas --}}
          <div class="md:col-span-2 space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Notas adicionales
              <span class="text-slate-400 font-normal text-xs">(Opcional)</span>
            </label>
            <textarea 
              name="notes" 
              rows="3" 
              class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" 
              placeholder="Observaciones, comentarios o información adicional sobre la cita..."
            >{{ old('notes', $prefill['notes']) }}</textarea>
            @error('notes')
              <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>

        {{-- Acciones --}}
        <div class="flex gap-3 pt-6 mt-6 border-t border-slate-200">
          @can('appointments.create')
            <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              Programar Cita
            </button>
          @endcan

          @can('appointments.view')
            <a href="{{ route('admin.appointments.index') }}" class="btn bg-rose-600 text-white hover:bg-rose-700 flex items-center gap-2 transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Cancelar
            </a>
          @endcan
        </div>

      </form>
    </div>

    {{-- Información de ayuda --}}
    <div class="mt-4 grid gap-4 md:grid-cols-2">
      <div class="card bg-blue-50 border-blue-200">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div>
            <h3 class="font-medium text-blue-800">Información importante</h3>
            <p class="text-sm text-blue-700 mt-1">
              Los horarios disponibles se calculan automáticamente según la disponibilidad del odontólogo seleccionado.
            </p>
          </div>
        </div>
      </div>

      <div class="card bg-amber-50 border-amber-200">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
          </svg>
          <div>
            <h3 class="font-medium text-amber-800">Requisitos</h3>
            <p class="text-sm text-amber-700 mt-1">
              Todos los campos marcados con <span class="text-red-500">*</span> son obligatorios para programar la cita.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
(() => {
  const $dentistHidden = document.getElementById('dentist_id');
  const $serviceHidden = document.getElementById('service_id');
  const $date          = document.getElementById('date');
  const $slots         = document.getElementById('slots');
  const $hint          = document.getElementById('slotsHint');

  const $patientInput  = document.getElementById('patient_input');
  const $serviceInput  = document.getElementById('service_input');
  const $dentistInput  = document.getElementById('dentist_input');

  // ===== helper para vincular input + datalist + hidden =====
  function bindDatalist(inputId, datalistId, hiddenId, onValidChange) {
    const input    = document.getElementById(inputId);
    const datalist = document.getElementById(datalistId);
    const hidden   = document.getElementById(hiddenId);

    if (!input || !datalist || !hidden) return;

    function sync() {
      const val = input.value.toLowerCase().trim();
      let foundId = '';

      datalist.querySelectorAll('option').forEach(opt => {
        if (opt.value.toLowerCase().trim() === val) {
          foundId = opt.dataset.id || '';
        }
      });

      hidden.value = foundId;

      if (foundId && typeof onValidChange === 'function') {
        onValidChange();
      }
    }

    input.addEventListener('change', sync);
    input.addEventListener('blur', sync);
  }

  // ========= HORARIOS / DISPONIBILIDAD =========
  function resetSlots(msg){
    if (!$slots) return;
    $slots.disabled = false;
    $slots.innerHTML = '<option value="">— Seleccione un horario —</option>';
    if ($hint) {
      $hint.innerHTML = `
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        ${msg || 'Seleccione servicio, odontólogo y fecha para ver horarios disponibles'}
      `;
    }
  }

  function showLoadingState() {
    if (!$slots) return;
    $slots.disabled = true;
    $slots.innerHTML = '<option value="">Cargando horarios disponibles...</option>';
    if ($hint) {
      $hint.innerHTML = `
        <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Buscando horarios disponibles...
      `;
    }
  }

  function showSuccessState(count) {
    if ($hint) {
      $hint.innerHTML = `
        <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        ${count} horario(s) disponible(s)
      `;
    }
  }

  function showErrorState(message) {
    if ($hint) {
      $hint.innerHTML = `
        <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        ${message}
      `;
    }
  }

  async function loadSlots(){
    if (!$dentistHidden || !$serviceHidden || !$date || !$slots) return;

    const d   = ($dentistHidden.value || '').trim();
    const s   = ($serviceHidden.value || '').trim();
    const day = ($date.value || '').trim();

    if(!d || !s || !day){ 
      resetSlots(); 
      return; 
    }

    showLoadingState();

    const url = `{{ route('admin.appointments.availability') }}?dentist_id=${encodeURIComponent(d)}&service_id=${encodeURIComponent(s)}&date=${encodeURIComponent(day)}`;

    try {
      const res  = await fetch(url, { headers: { 'Accept':'application/json' }});
      if(!res.ok) throw new Error('HTTP '+res.status);
      const list = await res.json();

      $slots.disabled = false;
      $slots.innerHTML = '<option value="">— Seleccione un horario —</option>';

      if (!Array.isArray(list) || list.length === 0){
        showErrorState('No hay horarios disponibles para la fecha seleccionada');
        return;
      }

      for (const h of list) {
        const opt = document.createElement('option');
        opt.value = /^\d{2}:\d{2}$/.test(h) ? (h + ':00') : h;
        opt.textContent = h;
        $slots.appendChild(opt);
      }
      
      showSuccessState(list.length);
    } catch (e) {
      console.error('loadSlots error', e);
      showErrorState('Error al cargar la disponibilidad. Intente nuevamente.');
    }
  }

  if (document.getElementById('formAppt')) {
    document.addEventListener('DOMContentLoaded', () => {
      // Vincular autocompletados
      bindDatalist('patient_input', 'patientsList', 'patient_id');
      bindDatalist('service_input', 'servicesList', 'service_id', loadSlots);
      bindDatalist('dentist_input', 'dentistsList', 'dentist_id', loadSlots);

      // Cambios directos en fecha también recargan horarios
      $date?.addEventListener('change', loadSlots);

      if($date && !$date.value){
        $date.setAttribute('min', new Date().toISOString().slice(0,10));
      }

      // Si ya hay servicio/odontólogo/fecha (ej: validación fallida) recarga horarios
      if ($dentistHidden?.value && $serviceHidden?.value && $date?.value) {
        loadSlots();
      }
    });
  }
})();
</script>
@endsection
