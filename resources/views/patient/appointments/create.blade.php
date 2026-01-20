@extends('patient.layout')
@section('title','Reservar cita')

@section('content')
  <div class="max-w-6xl mx-auto space-y-4">

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-900">Reservar cita</h1>
          <p class="text-sm text-slate-600 mt-1">
            Elige servicio, odontólogo y fecha. Los horarios se muestran automáticamente.
          </p>
        </div>

        <a href="{{ route('app.appointments.index') }}"
           class="btn btn-ghost border border-slate-200 hover:bg-slate-100 inline-flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Volver
        </a>
      </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">

      <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 lg:col-span-2">
        <form method="post" action="{{ route('app.appointments.store') }}" id="form-reserva" class="space-y-5">
          @csrf

          <div>
            <h2 class="text-sm font-semibold text-slate-900 mb-3">Datos de la reserva</h2>

            <div class="grid md:grid-cols-2 gap-3">
              {{-- Servicio (picker) --}}
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Servicio</label>
                <button type="button" id="btnService"
                        class="w-full text-left border border-slate-300 rounded-xl px-3 py-2 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200">
                  <div class="text-sm font-medium text-slate-900" id="serviceLabel">Selecciona un servicio…</div>
                  <div class="text-xs text-slate-500" id="serviceSub">Escribe para filtrar</div>
                </button>
                <input type="hidden" name="service_id" id="service_id" required>
              </div>

              {{-- Odontólogo (picker) --}}
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Odontólogo</label>
                <button type="button" id="btnDentist"
                        class="w-full text-left border border-slate-300 rounded-xl px-3 py-2 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200">
                  <div class="text-sm font-medium text-slate-900" id="dentistLabel">Selecciona un odontólogo…</div>
                  <div class="text-xs text-slate-500" id="dentistSub">Escribe para filtrar</div>
                </button>
                <input type="hidden" name="dentist_id" id="dentist_id" required>
              </div>

              {{-- Fecha --}}
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Fecha</label>
                <input type="date" name="date" id="date" required
                       value="{{ now()->toDateString() }}"
                       min="{{ now()->toDateString() }}"
                       class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-200">
              </div>

              {{-- Notas --}}
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Notas (opcional)</label>
                <input type="text" name="notes"
                       class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-200"
                       placeholder="Motivo breve (ej: dolor, control, limpieza)">
              </div>
            </div>
          </div>

          {{-- Horarios --}}
          <div class="pt-4 border-t border-slate-200">
            <div class="flex items-center justify-between gap-3 mb-2">
              <div>
                <h2 class="text-sm font-semibold text-slate-900">Horarios disponibles</h2>
                <p id="slotsHint" class="text-xs text-slate-500">Selecciona servicio y odontólogo.</p>
              </div>

              <button type="button" id="btn-recargar"
                      class="btn btn-ghost border border-slate-200 hover:bg-slate-100 inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 12a9 9 0 11-3-6.7M21 3v6h-6"/>
                </svg>
                Recargar
              </button>
            </div>

            <div id="slots" class="grid grid-cols-2 md:grid-cols-4 gap-2">
              <div class="text-sm text-slate-500 col-span-full">Aún no hay filtros completos.</div>
            </div>

            <input type="hidden" name="start_time" id="start_time" required>
          </div>

          <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row gap-2">
            <button class="btn bg-emerald-600 text-white hover:bg-emerald-700 inline-flex items-center justify-center gap-2"
                    id="btn-reservar" disabled>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              Reservar cita
            </button>

            <a href="{{ route('app.appointments.index') }}"
               class="btn btn-ghost border border-slate-200 hover:bg-slate-100 inline-flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Cancelar
            </a>
          </div>
        </form>
      </section>

      <aside class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-900">Tips</h3>
        <ul class="list-disc ms-5 text-sm text-slate-600 space-y-1">
          <li>Los “selects” abren un buscador y eliges con un clic.</li>
          <li>Los horarios se cargan cuando hay servicio + odontólogo + fecha.</li>
          <li>Si la fecha es hoy, los horarios pasados se muestran bloqueados.</li>
        </ul>
      </aside>

    </div>
  </div>

  {{-- MODAL PICKER --}}
  <div id="pickerBackdrop" class="fixed inset-0 bg-black/40 hidden z-50"></div>
  <div id="pickerModal" class="fixed inset-0 hidden z-50">
    <div class="min-h-full flex items-end sm:items-center justify-center p-3">
      <div class="bg-white w-full sm:max-w-lg rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between gap-3">
          <div>
            <div class="text-sm font-semibold text-slate-900" id="pickerTitle">Seleccionar</div>
            <div class="text-xs text-slate-500" id="pickerSubtitle">Escribe para filtrar</div>
          </div>
          <button type="button" id="pickerClose"
                  class="btn btn-ghost border border-slate-200 hover:bg-slate-100">
            Cerrar
          </button>
        </div>

        <div class="p-4 space-y-3">
          <input id="pickerSearch" type="text"
                 class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
                 placeholder="Buscar…">

          <div id="pickerList" class="max-h-72 overflow-y-auto border border-slate-200 rounded-xl divide-y"></div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';

  // ---- DOM
  const btnService   = document.getElementById('btnService');
  const btnDentist   = document.getElementById('btnDentist');

  const serviceId    = document.getElementById('service_id');
  const dentistId    = document.getElementById('dentist_id');

  const serviceLabel = document.getElementById('serviceLabel');
  const dentistLabel = document.getElementById('dentistLabel');

  const dateInput    = document.getElementById('date');

  const slotsBox     = document.getElementById('slots');
  const slotsHint    = document.getElementById('slotsHint');
  const startInput   = document.getElementById('start_time');
  const btnReservar  = document.getElementById('btn-reservar');
  const btnRecargar  = document.getElementById('btn-recargar');

  // ---- Modal
  const backdrop = document.getElementById('pickerBackdrop');
  const modal    = document.getElementById('pickerModal');
  const titleEl  = document.getElementById('pickerTitle');
  const subEl    = document.getElementById('pickerSubtitle');
  const closeBtn = document.getElementById('pickerClose');
  const searchEl = document.getElementById('pickerSearch');
  const listEl   = document.getElementById('pickerList');

  // ---- Data desde Blade
  const SERVICES = @json($services->map(fn($s)=>[
    'id' => (string)$s->id,
    'label' => $s->name.' ('.$s->duration_min.' min)',
  ])->values());

  const DENTISTS = @json($dentists->map(fn($d)=>[
    'id' => (string)$d->id,
    'label' => $d->name,
  ])->values());

  let currentPicker = null; // 'service' | 'dentist'

  function openPicker(type){
    currentPicker = type;
    const isService = type === 'service';
    titleEl.textContent = isService ? 'Seleccionar servicio' : 'Seleccionar odontólogo';
    subEl.textContent   = 'Escribe para filtrar';
    searchEl.value = '';
    modal.classList.remove('hidden');
    backdrop.classList.remove('hidden');
    renderPickerList(isService ? SERVICES : DENTISTS);
    setTimeout(()=>searchEl.focus(), 50);
  }

  function closePicker(){
    modal.classList.add('hidden');
    backdrop.classList.add('hidden');
    currentPicker = null;
  }

  function escapeHtml(str){
    return String(str).replace(/[&<>"']/g, (m) => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
  }

  function renderPickerList(items){
    const q = (searchEl.value || '').toLowerCase().trim();
    const filtered = !q ? items : items.filter(x => (x.label||'').toLowerCase().includes(q));

    listEl.innerHTML = '';
    if (!filtered.length){
      listEl.innerHTML = `<div class="p-3 text-sm text-slate-500">Sin resultados.</div>`;
      return;
    }

    filtered.forEach(item => {
      const row = document.createElement('button');
      row.type = 'button';
      row.className = 'w-full text-left p-3 hover:bg-slate-50 focus:outline-none';
      row.innerHTML = `<div class="text-sm font-medium text-slate-900">${escapeHtml(item.label)}</div>`;

      row.addEventListener('click', () => {
        if (currentPicker === 'service'){
          serviceId.value = item.id;
          serviceLabel.textContent = item.label;
        } else if (currentPicker === 'dentist'){
          dentistId.value = item.id;
          dentistLabel.textContent = item.label;
        }
        closePicker();
        fetchSlots();
      });

      listEl.appendChild(row);
    });
  }

  function enableReserveIfReady(){
    btnReservar.disabled = !(startInput.value && startInput.value.length >= 5);
  }

  function setNeedFilters(){
    slotsHint.textContent = 'Selecciona servicio y odontólogo para ver horarios.';
    slotsBox.innerHTML = '<div class="text-sm text-slate-500 col-span-full">Aún no hay filtros completos.</div>';
    startInput.value = '';
    enableReserveIfReady();
  }

  function setLoading(msg='Cargando horarios…'){
    slotsHint.textContent = msg;
    slotsBox.innerHTML = '<div class="text-sm text-slate-500 col-span-full">Cargando…</div>';
    startInput.value = '';
    enableReserveIfReady();
  }

  function setEmpty(msg='No hay horarios para esos filtros.'){
    slotsHint.textContent = msg;
    slotsBox.innerHTML = '<div class="text-sm text-slate-500 col-span-full">No hay horarios disponibles.</div>';
    startInput.value = '';
    enableReserveIfReady();
  }

  // ===========================
  // ✅ BLOQUEO DE HORAS PASADAS
  // ===========================
  function pad2(n){ return String(n).padStart(2,'0'); }

  function todayISO(){
    const d = new Date();
    return `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`;
  }

  function nowMinutes(){
    const d = new Date();
    return d.getHours()*60 + d.getMinutes();
  }

  function parseSlotMinutes(timeStr){
    if (!timeStr) return null;
    const parts = String(timeStr).split(':');
    const hh = parseInt(parts[0] || '0', 10);
    const mm = parseInt(parts[1] || '0', 10);
    return hh*60 + mm;
  }

  function isPastSlot(selectedDateISO, slotTime){
    if (selectedDateISO !== todayISO()) return false;
    const slotMin = parseSlotMinutes(slotTime);
    if (slotMin === null) return false;
    return slotMin <= nowMinutes(); // cambia a < si quieres permitir el minuto actual
  }

  function renderSlots(slots){
    slotsBox.innerHTML = '';
    if (!slots || !slots.length){
      setEmpty('No hay horarios para esos filtros.');
      return;
    }

    const selectedDate = dateInput.value || '';
    slotsHint.textContent = 'Selecciona un horario:';

    slots.forEach(s => {
      const timeRaw     = s.time || '';
      const timeShort   = timeRaw.substring(0,5);
      const dentistName = s.dentist ? s.dentist : 'Odontólogo';

      const past = isPastSlot(selectedDate, timeRaw);

      const btn = document.createElement('button');
      btn.type = 'button';

      if (past){
        btn.className =
          'border border-slate-200 rounded-xl px-3 py-2 text-sm bg-slate-50 text-slate-400 ' +
          'flex flex-col items-start gap-0.5 cursor-not-allowed opacity-80';
        btn.disabled = true;
      } else {
        btn.className =
          'border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 bg-white hover:bg-slate-50 ' +
          'transition flex flex-col items-start gap-0.5';
      }

      const time = document.createElement('div');
      time.className = past ? 'font-semibold text-slate-400' : 'font-semibold text-slate-900';
      time.textContent = timeShort;

      const dentist = document.createElement('div');
      dentist.className = 'text-xs text-slate-500';
      dentist.textContent = dentistName;

      btn.appendChild(time);
      btn.appendChild(dentist);

      if (past){
        const note = document.createElement('div');
        note.className = 'text-[11px] text-slate-400';
        note.textContent = 'Ya pasó';
        btn.appendChild(note);
      } else {
        btn.onclick = () => {
          startInput.value = (timeRaw && timeRaw.length === 5) ? (timeRaw + ':00') : (timeRaw || '');
          [...slotsBox.querySelectorAll('button')].forEach(b => b.classList.remove('ring-2','ring-blue-300','border-blue-300'));
          btn.classList.add('ring-2','ring-blue-300','border-blue-300');
          enableReserveIfReady();
        };
      }

      slotsBox.appendChild(btn);
    });
  }

  async function fetchSlots(){
    const service_id = serviceId.value || '';
    const dentist_id = dentistId.value || '';
    const date       = dateInput.value || '';

    if (!service_id || !dentist_id || !date){
      setNeedFilters();
      return;
    }

    setLoading('Cargando horarios…');

    const url = new URL('{{ route('app.appointments.availability') }}', window.location.origin);
    url.searchParams.set('service_id', service_id);
    url.searchParams.set('date', date);
    url.searchParams.set('dentist_id', dentist_id);

    try{
      const res = await fetch(url, {
        headers: {
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });
      const json = await res.json();
      renderSlots(json.slots || []);
    }catch(e){
      console.error(e);
      setEmpty('Error consultando disponibilidad.');
      alert('Error consultando disponibilidad');
    }
  }

  // ---- Events
  btnService.addEventListener('click', () => openPicker('service'));
  btnDentist.addEventListener('click', () => openPicker('dentist'));

  closeBtn.addEventListener('click', closePicker);
  backdrop.addEventListener('click', closePicker);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closePicker(); });

  searchEl.addEventListener('input', () => {
    renderPickerList(currentPicker === 'service' ? SERVICES : DENTISTS);
  });

  dateInput.addEventListener('change', fetchSlots);
  btnRecargar.addEventListener('click', fetchSlots);

  // Init
  setNeedFilters();
  enableReserveIfReady();
});
</script>
@endpush
