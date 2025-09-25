@extends('layouts.app')
@section('title','Odontograma')

@php
  $backUrl = request('appointment_id')
      ? route('admin.appointments.show', request('appointment_id'))
      : route('admin.patients.show', $patient);
@endphp

@section('header-actions')
  <a href="{{ $backUrl }}" class="btn btn-ghost">Volver</a>
@endsection

@php
  $upperL = [18,17,16,15,14,13,12,11];
  $upperR = [21,22,23,24,25,26,27,28];
  $lowerL = [48,47,46,45,44,43,42,41];
  $lowerR = [31,32,33,34,35,36,37,38];
@endphp

@section('content')
<style>
  :root{ --c-caries:#ef4444; --c-obtu:#f59e0b; --c-sell:#0ea5e9; --ring:#2563eb; }
  .panel { @apply bg-white rounded-xl p-4 shadow; }
  .toolbar label { @apply text-xs text-slate-500; }
  .tooth-grid { @apply grid gap-3 justify-center; grid-template-columns: repeat(8, minmax(36px,48px)); }
  .tooth { @apply relative rounded-lg border bg-white flex items-center justify-center p-1 cursor-pointer select-none; width:48px;height:54px; transition: box-shadow .15s, border-color .15s; }
  .tooth:hover { box-shadow: 0 0 0 2px #00000008 inset; }
  .tooth.selected { box-shadow: 0 0 0 2px var(--ring) inset; border-color: var(--ring); }
  .tooth[data-status="ausente"] .t-outline { opacity:.35 }
  .tooth{ position: relative; }
  .badge-dot { width:8px;height:8px;border-radius:50%; position:absolute; top:4px; left:4px; background:#10b981; z-index:2 }
  .tooth .absent-x{ position:absolute; inset:6px; pointer-events:none; z-index:2 }
  .tooth .absent-x::before,
  .tooth .absent-x::after{ content:""; position:absolute; left:0; right:0; top:50%; border-top:2px solid #94a3b8; transform-origin:center; }
  .tooth .absent-x::before{ transform:rotate(45deg) }
  .tooth .absent-x::after { transform:rotate(-45deg) }
  .cond-caries  { fill: color-mix(in srgb, var(--c-caries) 35%, transparent); stroke: var(--c-caries); }
  .cond-obturado{ fill: color-mix(in srgb, var(--c-obtu) 35%,  transparent); stroke: var(--c-obtu); }
  .cond-sellado { fill: color-mix(in srgb, var(--c-sell) 35%,  transparent); stroke: var(--c-sell); }
  .s-seg { stroke-width:1.2 }
  .legend li { @apply flex items-center gap-2 text-sm; }
  .dot { width:10px;height:10px;border-radius:50% }
  .dot.c{background:var(--c-caries)} .dot.o{background:var(--c-obtu)} .dot.s{background:var(--c-sell)}
  .dot.ok{background:#10b981} .dot.na{background:#94a3b8}
  .toast { position:fixed; top:16px; right:16px; background:#111827; color:#fff; padding:.5rem .75rem; border-radius:.5rem; box-shadow:0 6px 20px #00000033; opacity:.95; z-index:50 }

  /* Chips leyenda */
  .chip{display:inline-flex;align-items:center;gap:.375rem;font-size:.75rem;padding:.125rem .5rem;border-radius:.5rem}
  .chip.sano{background:#d1fae5;color:#065f46}
  .chip.ausente{background:#e5e7eb;color:#374151}
  .chip.caries{background:#fee2e2;color:#991b1b}
  .chip.obturado{background:#fef3c7;color:#92400e}
  .chip.sellado{background:#e0f2fe;color:#075985}

  /* Minimapa superficies */
  .mini-grid{display:grid;grid-template-columns:repeat(5,16px);gap:4px;align-items:center}
  .mini-cell{width:16px;height:16px;border-radius:3px;border:1px solid #cbd5e1}
  .mini-O{grid-column:3}
  .mini-M{grid-column:1}
  .mini-D{grid-column:5}
  .mini-B{grid-column:2}
  .mini-L{grid-column:4}
</style>

<div class="grid lg:grid-cols-4 gap-4">
  <section class="panel lg:col-span-3">
    <div class="toolbar grid md:grid-cols-6 gap-3 mb-3">
      <div>
        <label>Selección</label>
        <select id="mode" class="w-full border rounded px-2 py-1">
          <option value="single">Individual</option>
          <option value="multi">Múltiple</option>
        </select>
      </div>
      <div class="md:col-span-2 flex items-end gap-2">
        <div class="flex-1">
          <label>Estado pieza</label>
          <select id="statePiece" class="w-full border rounded px-2 py-1">
            <option value="">—</option>
            <option value="sano">Sano</option>
            <option value="ausente">Ausente</option>
          </select>
        </div>
        <button id="applyPiece" class="btn bg-slate-100">Aplicar</button>
      </div>
      <div class="md:col-span-2 flex items-end gap-2">
        <div class="flex-1">
          <label>Superficie</label>
          <select id="surface" class="w-full border rounded px-2 py-1">
            <option value="">—</option>
            <option value="O">Oclusal/Incisal</option>
            <option value="M">Mesial</option>
            <option value="D">Distal</option>
            <option value="B">Vestibular/Bucal</option>
            <option value="L">Lingual/Palatina</option>
          </select>
        </div>
        <div class="flex-1">
          <label>Condición</label>
          <select id="condition" class="w-full border rounded px-2 py-1">
            <option value="">—</option>
            <option value="caries">Caries</option>
            <option value="obturado">Obturado</option>
            <option value="sellado">Sellado</option>
          </select>
        </div>
        <button id="applySurface" class="btn bg-slate-100">Aplicar</button>
      </div>
      <div class="flex items-end gap-2">
        <button id="clearSel" class="btn bg-slate-100 w-full">Limpiar pieza(s)</button>
      </div>
    </div>

    {{-- ARCO SUPERIOR --}}
    <div class="text-xs text-slate-500 mb-1">Arcada superior</div>
    <div class="tooth-grid mb-4">
      @foreach(array_merge($upperL,$upperR) as $code)
        <button class="tooth"
                data-code="{{ $code }}"
                data-status="{{ optional($teethByCode->get($code))->status ?? '' }}">
          <svg viewBox="0 0 40 44" width="36" height="40">
            <path class="t-outline" d="M20 1c7 0 12 5 12 12 0 10-5 17-7 26-1 4-9 4-10 0-2-9-7-16-7-26C8 6 13 1 20 1Z" fill="#fff" stroke="#cbd5e1"/>
            <rect class="s-seg s-O" x="11" y="8"  width="18" height="8"  rx="2" />
            <rect class="s-seg s-M" x="6"  y="14" width="7"  height="12" rx="2" />
            <rect class="s-seg s-D" x="27" y="14" width="7"  height="12" rx="2" />
            <rect class="s-seg s-B" x="13" y="18" width="14" height="9"  rx="2" />
            <rect class="s-seg s-L" x="13" y="29" width="14" height="7"  rx="2" />
          </svg>
          <span class="badge-dot" style="display:none"></span>
          <span class="absent-x"  style="display:none"></span>
          <span class="absolute bottom-1 right-1 text-[10px] text-slate-400">{{ $code }}</span>
        </button>
      @endforeach
    </div>

    {{-- ARCO INFERIOR --}}
    <div class="text-xs text-slate-500 mb-1">Arcada inferior</div>
    <div class="tooth-grid">
      @foreach(array_merge($lowerL,$lowerR) as $code)
        <button class="tooth"
                data-code="{{ $code }}"
                data-status="{{ optional($teethByCode->get($code))->status ?? '' }}">
          <svg viewBox="0 0 40 44" width="36" height="40">
            <path class="t-outline" d="M20 1c7 0 12 5 12 12 0 10-5 17-7 26-1 4-9 4-10 0-2-9-7-16-7-26C8 6 13 1 20 1Z" fill="#fff" stroke="#cbd5e1"/>
            <rect class="s-seg s-O" x="11" y="8"  width="18" height="8"  rx="2" />
            <rect class="s-seg s-M" x="6"  y="14" width="7"  height="12" rx="2" />
            <rect class="s-seg s-D" x="27" y="14" width="7"  height="12" rx="2" />
            <rect class="s-seg s-B" x="13" y="18" width="14" height="9"  rx="2" />
            <rect class="s-seg s-L" x="13" y="29" width="14" height="7"  rx="2" />
          </svg>
          <span class="badge-dot" style="display:none"></span>
          <span class="absent-x"  style="display:none"></span>
          <span class="absolute bottom-1 right-1 text-[10px] text-slate-400">{{ $code }}</span>
        </button>
      @endforeach
    </div>

    <div class="mt-4">
      <button id="saveAll" class="btn bg-blue-600 text-white">Guardar</button>
    </div>
  </section>

  {{-- Panel derecho --}}
  <aside class="panel">
    <h4 class="font-semibold mb-2">Leyenda</h4>
    <ul class="space-y-1 mb-4 text-sm">
      <li><span class="chip sano">Sano</span></li>
      <li><span class="chip caries">Caries</span></li>
      <li><span class="chip obturado">Obturado</span></li>
      <li><span class="chip sellado">Sellado</span></li>
      <li><span class="chip ausente">Ausente</span></li>
    </ul>

    <div class="border-t my-3"></div>

    <h4 class="font-semibold">Pieza seleccionada</h4>
    <div id="selHeader" class="text-sm text-slate-500 mb-2">Selecciona una pieza…</div>
    <div id="selStatus" class="mb-2"></div>

    <div id="selMini" class="mini-grid mb-2" style="display:none">
      <div class="mini-cell mini-M" title="Mesial"></div>
      <div class="mini-cell mini-B" title="Vestibular/Bucal"></div>
      <div class="mini-cell mini-O" title="Oclusal/Incisal"></div>
      <div class="mini-cell mini-L" title="Lingual/Palatina"></div>
      <div class="mini-cell mini-D" title="Distal"></div>
    </div>

    <ul id="selSurfaces" class="text-sm space-y-1 mb-4"></ul>

    <h4 class="font-semibold">Nota</h4>
    <textarea id="note" rows="6" class="w-full border rounded p-2 text-sm" placeholder="Anota observaciones para la(s) pieza(s)…"></textarea>
    <button id="saveNote" class="btn bg-slate-100 mt-2">Guardar nota</button>

    <p class="mt-4 text-xs text-slate-500">
      Tip: selecciona varias piezas con “Múltiple” para aplicar la misma condición o estado.
    </p>
  </aside>
</div>

{{-- Estado inicial --}}
<script>
  const APPT_ID = {{ request('appointment_id') ? (int)request('appointment_id') : 'null' }};
  const TEETH_INIT = {!! json_encode($teethInit, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!};

  function applyStateToTooth(code, state){
    const el = document.querySelector(`.tooth[data-code="${code}"]`);
    if(!el) return;
    el.dataset.status = state.status || '';

    const dot = el.querySelector('.badge-dot');
    const cross = el.querySelector('.absent-x');
    if(state.status === 'ausente'){ dot.style.display='none'; cross.style.display=''; }
    else if(state.status === 'sano'){ cross.style.display='none'; dot.style.display=''; }
    else{ cross.style.display='none'; dot.style.display='none'; }

    ['O','M','D','B','L'].forEach(s => {
      const seg = el.querySelector(`.s-${s}`);
      seg?.classList.remove('cond-caries','cond-obturado','cond-sellado');
    });
    (state.surfaces || []).forEach(s => {
      const seg = el.querySelector(`.s-${s.surface}`);
      seg?.classList.add('cond-'+s.condition);
    });
  }

  Object.entries(TEETH_INIT || {}).forEach(([code, state]) => applyStateToTooth(code, state));
</script>

<script>
(() => {
  const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const MODE        = document.getElementById('mode');
  const STATE_PIECE = document.getElementById('statePiece');
  const APPLY_P     = document.getElementById('applyPiece');
  const SURFACE     = document.getElementById('surface');
  const CONDITION   = document.getElementById('condition');
  const APPLY_S     = document.getElementById('applySurface');
  const CLEAR       = document.getElementById('clearSel');
  const SAVE_ALL    = document.getElementById('saveAll');
  const SAVE_NOTE   = document.getElementById('saveNote');
  const NOTE        = document.getElementById('note');

  const $teeth   = Array.from(document.querySelectorAll('.tooth'));
  const selected = new Set();
  const changes  = new Map(); // code -> { status, notes, surfaces: {O:'caries',...} }

  const SURF_LABELS = {O:'Oclusal/Incisal', M:'Mesial', D:'Distal', B:'Vestibular/Bucal', L:'Lingual/Palatina'};

  // Panel elementos
  const SEL_HEADER   = document.getElementById('selHeader');
  const SEL_STATUS   = document.getElementById('selStatus');
  const SEL_MINI     = document.getElementById('selMini');
  const SEL_SURFACES = document.getElementById('selSurfaces');

  // Lee el estado actual (DOM + cambios pendientes)
  function readState(code){
    const el = document.querySelector(`.tooth[data-code="${code}"]`);
    const state = { status: el?.dataset?.status || null, surfaces:{} };
    ['O','M','D','B','L'].forEach(s=>{
      const seg = el?.querySelector(`.s-${s}`);
      if(!seg) return;
      if (seg.classList.contains('cond-caries'))   state.surfaces[s]='caries';
      if (seg.classList.contains('cond-obturado')) state.surfaces[s]='obturado';
      if (seg.classList.contains('cond-sellado'))  state.surfaces[s]='sellado';
    });
    const pending = changes.get(code);
    if (pending){
      if (pending.status !== undefined) state.status = pending.status;
      if (pending.surfaces) state.surfaces = {...state.surfaces, ...pending.surfaces};
    }
    return state;
  }

  // Rellena panel derecho
  function renderSelectionPanel(){
    if (selected.size === 0){
      SEL_HEADER.textContent = 'Selecciona una pieza…';
      SEL_STATUS.innerHTML = '';
      SEL_MINI.style.display = 'none';
      SEL_SURFACES.innerHTML = '';
      return;
    }
    if (selected.size > 1){
      SEL_HEADER.textContent = `${selected.size} piezas seleccionadas`;
      SEL_STATUS.innerHTML = '';
      SEL_MINI.style.display = 'none';
      SEL_SURFACES.innerHTML = '';
      return;
    }
    const code = [...selected][0];
    const st   = readState(code);
    SEL_HEADER.textContent = `Pieza ${code}`;
    const statusChip = st.status ? `<span class="chip ${st.status}">${st.status === 'sano' ? 'Sano' : 'Ausente'}</span>` : '';
    SEL_STATUS.innerHTML = statusChip;

    SEL_MINI.style.display = '';
    SEL_MINI.querySelectorAll('.mini-cell').forEach(c=>c.style.background='');
    Object.entries(st.surfaces).forEach(([s,cond])=>{
      const cell = SEL_MINI.querySelector(`.mini-${s}`);
      if (!cell) return;
      cell.style.background = ({caries:'#fecaca', obturado:'#fde68a', sellado:'#bae6fd'})[cond] || '';
    });

    const items = Object.entries(st.surfaces)
      .map(([s,cond])=>`<li><span class="chip ${cond}">${cond}</span> <span class="text-slate-600">${SURF_LABELS[s]}</span></li>`);
    SEL_SURFACES.innerHTML = items.length ? items.join('') : '<li class="text-slate-500">Sin hallazgos en superficies.</li>';
  }

  const setSelected = ($el, on) => {
    $el.classList.toggle('selected', on);
    const code = $el.dataset.code;
    on ? selected.add(code) : selected.delete(code);
  };

  const ensureItem = (code) => {
    if (!changes.has(code)) {
      const el = document.querySelector(`.tooth[data-code="${code}"]`);
      changes.set(code, { status: el?.dataset?.status || null, notes: null, surfaces: {} });
    }
    return changes.get(code);
  };

  const setBadgeFor = (code, status) => {
    const el = document.querySelector(`.tooth[data-code="${code}"]`);
    const dot = el.querySelector('.badge-dot'); const cross = el.querySelector('.absent-x');
    if (status === 'ausente'){ dot.style.display='none'; cross.style.display=''; }
    else if (status === 'sano'){ cross.style.display='none'; dot.style.display=''; }
    else { cross.style.display='none'; dot.style.display='none'; }
  };

  const paintSurface = (code, s, cond) => {
    const el = document.querySelector(`.tooth[data-code="${code}"] .s-${s}`);
    if (!el) return;
    el.classList.remove('cond-caries','cond-obturado','cond-sellado');
    if (cond) el.classList.add('cond-'+cond);
  };

  const paintAllSurfaces = (code) => ['O','M','D','B','L'].forEach(s => paintSurface(code, s, null));

  // Selección
  $teeth.forEach($t => {
    $t.addEventListener('click', () => {
      if (MODE.value === 'single') $teeth.forEach(el => setSelected(el, false));
      setSelected($t, !$t.classList.contains('selected'));
      renderSelectionPanel();
    });
  });

  // Aplicar estado
  APPLY_P.addEventListener('click', () => {
    if (!selected.size) return;
    const val = STATE_PIECE.value || null;
    [...selected].forEach(code => {
      const it = ensureItem(code);
      it.status = val;
      const el = document.querySelector(`.tooth[data-code="${code}"]`);
      el.dataset.status = val || '';
      setBadgeFor(code, val);
      if (val === 'ausente') { it.surfaces = {}; paintAllSurfaces(code); }
    });
    renderSelectionPanel();
  });

  // Aplicar superficie/condición
  APPLY_S.addEventListener('click', () => {
    if (!selected.size) return;
    const s = SURFACE.value, c = CONDITION.value;
    if (!s || !c) return;
    [...selected].forEach(code => {
      const it = ensureItem(code);
      if (it.status === 'ausente') it.status = 'sano';
      paintSurface(code, s, c);
      it.surfaces[s] = c;
      const el = document.querySelector(`.tooth[data-code="${code}"]`);
      el.dataset.status = it.status || '';
      setBadgeFor(code, it.status);
    });
    renderSelectionPanel();
  });

  // Limpiar
  CLEAR.addEventListener('click', () => {
    if (!selected.size) return;
    [...selected].forEach(code => {
      const it = ensureItem(code);
      it.status = null; it.surfaces = {};
      const el = document.querySelector(`.tooth[data-code="${code}"]`);
      el.dataset.status = '';
      setBadgeFor(code, null);
      paintAllSurfaces(code);
    });
    renderSelectionPanel();
  });

  // Guardar nota local
  SAVE_NOTE.addEventListener('click', () => {
    const text = NOTE.value.trim();
    if (!selected.size || !text) return;
    [...selected].forEach(code => ensureItem(code).notes = text);
    NOTE.value = '';
    toast('Nota aplicada a la selección');
  });

  // Guardar (si tu endpoint solo devuelve {ok:true}, puedes quitar el redirect)
  SAVE_ALL.addEventListener('click', async () => {
    if (changes.size === 0) { toast('No hay cambios'); return; }
    const items = Array.from(changes.entries()).map(([code, it]) => ({
      tooth_code: code,
      status: it.status || null,
      notes: it.notes || null,
      surfaces: Object.entries(it.surfaces).map(([surface, condition]) => ({ surface, condition }))
    }));
    try {
      const res = await fetch("{{ route('admin.odontograms.teeth.upsert',$odontogram) }}", {
  method: 'POST',
  headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
  body: JSON.stringify({ items, appointment_id: APPT_ID })
});
const data = await res.json();
if (data.redirect) location.href = data.redirect;
      if (!res.ok) throw new Error('HTTP '+res.status);
      // const data = await res.json();
      changes.clear();
      toast('Odontograma guardado ✅');
      // if (data.redirect) location.href = data.redirect;
    } catch (e) {
      console.error(e);
      toast('Error al guardar');
    }
  });

  function toast(msg){ const t=document.createElement('div'); t.className='toast'; t.textContent=msg; document.body.appendChild(t); setTimeout(()=>t.remove(),1500); }
})();
</script>
@endsection
