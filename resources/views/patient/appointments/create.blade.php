@extends('layouts.app')
@section('title','Reservar cita')

@section('content')
  <div class="grid gap-4 md:grid-cols-3">
    <section class="card md:col-span-2">
      <form method="post" action="{{ route('app.appointments.store') }}" id="form-reserva" class="grid gap-3">
        @csrf

        <div class="grid md:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs text-slate-500 mb-1">Servicio</label>
            <select name="service_id" id="service_id" class="w-full border rounded px-3 py-2" required>
              <option value="">Selecciona…</option>
              @foreach($services as $s)
                <option value="{{ $s->id }}" data-duration="{{ $s->duration_min }}">{{ $s->name }} ({{ $s->duration_min }} min)</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-xs text-slate-500 mb-1">Odontólogo (opcional)</label>
            <select id="dentist_id" name="dentist_id" class="w-full border rounded px-3 py-2">
              <option value="">Cualquiera</option>
              @foreach($dentists as $d)
                <option value="{{ $d->id }}">{{ $d->name }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-xs text-slate-500 mb-1">Fecha</label>
            <input type="date" name="date" id="date" class="w-full border rounded px-3 py-2" required min="{{ now()->toDateString() }}">
          </div>

          <div>
            <label class="block text-xs text-slate-500 mb-1">Notas (opcional)</label>
            <input type="text" name="notes" class="w-full border rounded px-3 py-2" placeholder="Motivo breve">
          </div>
        </div>

        <div class="border-t pt-3">
          <div class="flex items-center justify-between mb-2">
            <h3 class="font-semibold">Horarios disponibles</h3>
            <button type="button" id="btn-buscar" class="btn btn-ghost">Buscar</button>
          </div>

          <div id="slots" class="grid grid-cols-2 md:grid-cols-4 gap-2">
            {{-- se llena por JS --}}
          </div>
          <input type="hidden" name="start_time" id="start_time" required>
        </div>

        <div class="mt-2">
          <button class="btn btn-primary">Reservar</button>
          <a href="{{ route('app.appointments.index') }}" class="btn btn-ghost">Cancelar</a>
        </div>
      </form>
    </section>

    <aside class="card">
      <h3 class="font-semibold mb-2">Instrucciones</h3>
      <ol class="list-decimal ms-5 text-sm text-slate-600 space-y-1">
        <li>Elige el servicio y (opcional) el odontólogo.</li>
        <li>Selecciona una fecha y pulsa <b>Buscar</b>.</li>
        <li>Haz clic en un horario para seleccionarlo.</li>
        <li>Presiona <b>Reservar</b>.</li>
      </ol>
    </aside>
  </div>
@endsection

@section('scripts')
<script>
const $ = (sel) => document.querySelector(sel);
const csrf = document.querySelector('meta[name=csrf-token]').content;

const slotsBox   = $('#slots');
const startInput = $('#start_time');

function renderSlots(slots) {
  slotsBox.innerHTML = '';
  if (!slots || !slots.length) {
    slotsBox.innerHTML = '<div class="text-sm text-slate-500 col-span-full">No hay horarios para esos filtros.</div>';
    return;
  }
  slots.forEach(s => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-ghost';
    btn.textContent = `${s.time} · ${s.dentist}`;
    btn.onclick = () => {
      startInput.value = s.time.length === 5 ? s.time + ':00' : s.time; // HH:MM:SS
      // setea campo dentist si usuario no eligió uno
      const dSel = document.getElementById('dentist_id');
      if (!dSel.value) dSel.value = s.dentist_id;
      // marca selección visual
      [...slotsBox.querySelectorAll('button')].forEach(b=>b.classList.remove('nav-active'));
      btn.classList.add('nav-active');
    };
    slotsBox.appendChild(btn);
  });
}

document.getElementById('btn-buscar').addEventListener('click', async () => {
  const service_id = document.getElementById('service_id').value;
  const date       = document.getElementById('date').value;
  const dentist_id = document.getElementById('dentist_id').value;

  if (!service_id || !date) {
    alert('Selecciona servicio y fecha');
    return;
  }

  const url = new URL('{{ route('app.appointments.availability') }}', window.location.origin);
  url.searchParams.set('service_id', service_id);
  url.searchParams.set('date', date);
  if (dentist_id) url.searchParams.set('dentist_id', dentist_id);

  try {
    const res = await fetch(url, { headers: { 'X-CSRF-TOKEN': csrf }});
    const json = await res.json();
    renderSlots(json.slots || []);
  } catch (e) {
    console.error(e);
    alert('Error consultando disponibilidad');
  }
});
</script>
@endsection
