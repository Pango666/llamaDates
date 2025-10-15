@extends('layouts.app')
@section('title','Nueva Cita')

@section('header-actions')
  <a href="{{ route('admin.appointments.index') }}" class="btn btn-ghost">
    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver al listado
  </a>
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

      <form method="post" action="{{ route('admin.appointments.store') }}" id="formAppt">
        @csrf

        <div class="grid gap-6 md:grid-cols-2">
          {{-- Paciente --}}
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Paciente
              <span class="text-red-500">*</span>
            </label>
            <select name="patient_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
              <option value="">— Seleccione un paciente —</option>
              @foreach($patients as $p)
                <option value="{{ $p->id }}" @selected(old('patient_id', $prefill['patient_id'])==$p->id)>
                  {{ $p->last_name }}, {{ $p->first_name }}
                </option>
              @endforeach
            </select>
            @error('patient_id')
              <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Servicio --}}
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
              Servicio
              <span class="text-red-500">*</span>
            </label>
            <select name="service_id" id="service_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
              <option value="">— Seleccione un servicio —</option>
              @foreach($services as $s)
                <option value="{{ $s->id }}" @selected(old('service_id', $prefill['service_id'])==$s->id)>
                  {{ $s->name }}
                </option>
              @endforeach
            </select>
            @error('service_id')
              <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Odontólogo --}}
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Odontólogo
              <span class="text-red-500">*</span>
            </label>
            <select name="dentist_id" id="dentist_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
              <option value="">— Seleccione un odontólogo —</option>
              @foreach($dentists as $d)
                <option value="{{ $d->id }}" @selected(old('dentist_id', $prefill['dentist_id'])==$d->id)>
                  {{ $d->name }}
                </option>
              @endforeach
            </select>
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
          <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Programar Cita
          </button>
          <a href="{{ route('admin.appointments.index') }}" class="btn btn-ghost flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Cancelar
          </a>
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
  const $dentist = document.getElementById('dentist_id');
  const $service = document.getElementById('service_id');
  const $date    = document.getElementById('date');
  const $slots   = document.getElementById('slots');
  const $hint    = document.getElementById('slotsHint');

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
    if (!$dentist || !$service || !$date || !$slots) return;

    const d = ($dentist.value || '').trim();
    const s = ($service.value || '').trim();
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

  // Event listeners
  if (document.getElementById('formAppt')) {
    [$dentist, $service, $date].forEach(element => {
      element?.addEventListener('change', loadSlots);
    });

    // Precarga si ya vienen valores
    document.addEventListener('DOMContentLoaded', () => {
      if($date && !$date.value){
        $date.setAttribute('min', new Date().toISOString().slice(0,10));
      }
      if ($dentist?.value && $service?.value && $date?.value) {
        loadSlots();
      }
    });
  }
})();
</script>
@endsection