@extends('layouts.app')
@section('title','Nueva cita')

@section('header-actions')
  <a href="{{ route('admin.appointments.index') }}" class="btn btn-ghost">Volver</a>
@endsection

@section('content')
  <form method="post" action="{{ route('admin.appointments.store') }}" id="formAppt" class="card">
    @csrf

    <div class="grid gap-4 md:grid-cols-2">
      <div>
        <label class="block text-xs text-slate-500 mb-1">Paciente</label>
        <select name="patient_id" class="w-full border rounded px-2 py-2" required>
          <option value="">— Selecciona —</option>
          @foreach($patients as $p)
            <option value="{{ $p->id }}" @selected(old('patient_id', $prefill['patient_id'])==$p->id)>
              {{ $p->last_name }}, {{ $p->first_name }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-xs text-slate-500 mb-1">Servicio</label>
        <select name="service_id" id="service_id" class="w-full border rounded px-2 py-2" required>
          <option value="">— Selecciona —</option>
          @foreach($services as $s)
            <option value="{{ $s->id }}" @selected(old('service_id', $prefill['service_id'])==$s->id)>
              {{ $s->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-xs text-slate-500 mb-1">Odontólogo</label>
        <select name="dentist_id" id="dentist_id" class="w-full border rounded px-2 py-2" required>
          <option value="">— Selecciona —</option>
          @foreach($dentists as $d)
            <option value="{{ $d->id }}" @selected(old('dentist_id', $prefill['dentist_id'])==$d->id)>
              {{ $d->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-xs text-slate-500 mb-1">Fecha</label>
        <input
          type="date"
          id="date"
          name="date"
          value="{{ old('date', $prefill['date'] ?? now()->toDateString()) }}"
          class="w-full border rounded px-2 py-2"
          required
          min="{{ now()->toDateString() }}"
        >
      </div>

      <div class="md:col-span-2">
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="block text-xs text-slate-500 mb-1">Hora</label>
            <select name="start_time" id="slots" class="w-full border rounded px-2 py-2" required>
              <option value="">— Selecciona —</option>
            </select>
            <small id="slotsHint" class="text-slate-500 text-xs">
              Selecciona servicio, odontólogo y fecha para ver horarios.
            </small>
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Notas <span class="text-slate-400">(Opcional)</span></label>
            <textarea name="notes" rows="3" class="w-full border rounded px-2 py-2" placeholder="Observaciones">{{ old('notes', $prefill['notes']) }}</textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="pt-3">
      <button class="btn btn-primary">Guardar</button>
      <a href="{{ route('admin.appointments.index') }}" class="btn btn-ghost">Cancelar</a>
    </div>
  </form>
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
    $slots.innerHTML = '<option value="">— Selecciona —</option>';
    if ($hint) $hint.textContent = msg || 'Selecciona servicio, odontólogo y fecha para ver horarios.';
  }

  async function loadSlots(){
    if (!$dentist || !$service || !$date || !$slots) return;

    const d = ($dentist.value || '').trim();
    const s = ($service.value || '').trim();
    const day = ($date.value || '').trim();

    if(!d || !s || !day){ resetSlots(); return; }

    $slots.disabled = true;
    $slots.innerHTML = '<option>Cargando…</option>';

    const url = `{{ route('admin.appointments.availability') }}?dentist_id=${encodeURIComponent(d)}&service_id=${encodeURIComponent(s)}&date=${encodeURIComponent(day)}`;

    try {
      const res  = await fetch(url, { headers: { 'Accept':'application/json' }});
      if(!res.ok) throw new Error('HTTP '+res.status);
      const list = await res.json();
      console.log('[citas] slots', list);

      $slots.disabled = false;
      $slots.innerHTML = '<option value="">— Selecciona —</option>';

      if (!Array.isArray(list) || list.length === 0){
        if ($hint) $hint.textContent = 'Sin horarios disponibles para esa fecha.';
        return;
      }

      for (const h of list) {
        const opt = document.createElement('option');
        opt.value = /^\d{2}:\d{2}$/.test(h) ? (h + ':00') : h; // normaliza a HH:MM:SS
        opt.textContent = h;
        $slots.appendChild(opt);
      }
      if ($hint) $hint.textContent = `${list.length} horarios disponibles`;
    } catch (e) {
      console.error('loadSlots error', e);
      resetSlots('No se pudo cargar la disponibilidad.');
    }
  }

  // listeners
  if (document.getElementById('formAppt')) {
    $dentist?.addEventListener('change', loadSlots);
    $service?.addEventListener('change', loadSlots);
    $date?.addEventListener('change', loadSlots);

    // precarga si ya vienen valores
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