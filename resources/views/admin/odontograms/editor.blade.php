@extends('layouts.app')
@section('title', 'Odontograma - ' . $patient->full_name)

@php
  $backUrl = request('appointment_id')
      ? route('admin.appointments.show', request('appointment_id'))
      : route('admin.patients.show', $patient);

  // ===== Mapear forma SVG por código FDI =====
  function getToothSVG($code) {
      $shapes = [
          // Incisivos Centrales
          11 => 'incisivo_central', 21 => 'incisivo_central',
          31 => 'incisivo_central', 41 => 'incisivo_central',
          // Incisivos Laterales
          12 => 'incisivo_lateral', 22 => 'incisivo_lateral',
          32 => 'incisivo_lateral', 42 => 'incisivo_lateral',
          // Caninos
          13 => 'canino_superior', 23 => 'canino_superior',
          33 => 'canino_inferior', 43 => 'canino_inferior',
          // Premolares
          14 => 'premolar_1_superior', 24 => 'premolar_1_superior',
          15 => 'premolar_2_superior', 25 => 'premolar_2_superior',
          34 => 'premolar_1_inferior', 44 => 'premolar_1_inferior',
          35 => 'premolar_2_inferior', 45 => 'premolar_2_inferior',
          // Molares
          16 => 'molar_1_superior', 26 => 'molar_1_superior',
          17 => 'molar_2_superior', 27 => 'molar_2_superior',
          18 => 'molar_3_superior', 28 => 'molar_3_superior',
          36 => 'molar_1_inferior', 46 => 'molar_1_inferior',
          37 => 'molar_2_inferior', 47 => 'molar_2_inferior',
          38 => 'molar_3_inferior', 48 => 'molar_3_inferior',
      ];
      return $shapes[$code] ?? 'incisivo_central';
  }

  // Códigos por arcada (orden odontograma clásico)
  $upperCodes = [18,17,16,15,14,13,12,11, 21,22,23,24,25,26,27,28];
  $lowerCodes = [48,47,46,45,44,43,42,41, 31,32,33,34,35,36,37,38];
@endphp

@section('header-actions')
  <a href="{{ $backUrl }}" class="btn btn-ghost flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver
  </a>
@endsection

@section('content')
<style>
  :root { --c-caries:#ef4444; --c-obtu:#f59e0b; --c-sell:#0ea5e9; }
  .panel{background:#fff;border-radius:.75rem;padding:1.5rem;box-shadow:0 1px 2px rgba(0,0,0,.05);border:1px solid #e2e8f0}
  .toolbar label{display:block;font-size:.875rem;font-weight:500;color:#334155;margin-bottom:.5rem}

  /* ====== FILAS (estilo ficha odontograma) ====== */
  .row-odontograma{position:relative;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;min-height:160px;overflow:hidden}
  .row-odontograma .baseline{position:absolute;left:24px;right:24px;top:50%;height:2px;background:#e2e8f0;border-radius:2px}
  .row-odontograma .midline{position:absolute;top:10px;bottom:10px;left:50%;width:2px;background:#e2e8f0;border-radius:2px}
  .teeth-row{display:grid;grid-template-columns:repeat(16, minmax(40px, 56px));gap:10px;align-items:center;justify-content:center;padding:16px 24px;height:100%}
  .teeth-row.sup{align-items:flex-end;padding-bottom:22px}
  .teeth-row.inf{align-items:flex-start;padding-top:22px}

  /* Pieza: SIN cuadro (solo el SVG) */
  .tooth-arcada{position:relative;width:56px;height:64px;display:flex;align-items:center;justify-content:center;background:transparent;border:none;cursor:pointer;transition:transform .15s ease, filter .15s ease}
  .tooth-arcada:hover{transform:translateY(-2px)}
  .tooth-arcada.selected{outline:2px solid rgba(37,99,235,.9);outline-offset:4px;border-radius:.5rem;filter:drop-shadow(0 6px 14px rgba(2,6,23,.08))}

  /* Estados */
  .tooth-ausente svg{opacity:.3}
  .tooth-ausente::before{content:"✕";position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#64748b;font-weight:700;font-size:20px;z-index:2}
  .badge-dot{position:absolute;top:0;left:0;transform:translate(-30%,-30%);width:10px;height:10px;border-radius:50%;background:#10b981;display:none;box-shadow:0 1px 3px rgba(0,0,0,.12)}
  .tooth-sano .badge-dot{display:block}
  .tooth-number{position:absolute;left:50%;transform:translateX(-50%);font-size:10px;font-weight:600;color:#475569;background:rgba(255,255,255,.85);border-radius:4px;padding:1px 4px}
  .teeth-row.sup .tooth-number{bottom:-14px}
  .teeth-row.inf .tooth-number{top:-14px}

  /* Condiciones por superficie */
  .cond-caries{fill:color-mix(in srgb,var(--c-caries) 35%,transparent)!important;stroke:var(--c-caries)!important;stroke-width:1.6!important}
  .cond-obturado{fill:color-mix(in srgb,var(--c-obtu) 35%,transparent)!important;stroke:#d97706!important;stroke-width:1.6!important}
  .cond-sellado{fill:color-mix(in srgb,var(--c-sell) 30%,transparent)!important;stroke:#0284c7!important;stroke-width:1.6!important}

  .section-title{font-size:.875rem;font-weight:600;color:#334155;margin-bottom:.75rem;padding-bottom:.5rem;border-bottom:1px solid #e2e8f0}
  .btn-compact{display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.5rem .75rem;border-radius:.5rem;font-weight:500;transition:all .2s ease;font-size:.875rem;white-space:nowrap}
  .btn-compact.primary{background:#2563eb;color:#fff}.btn-compact.primary:hover{background:#1d4ed8}
  .btn-compact.secondary{background:#f1f5f9;color:#334155;border:1px solid #cbd5e1}.btn-compact.secondary:hover{background:#e2e8f0}

  .legend li{display:flex;align-items:center;gap:.75rem;font-size:.875rem;margin-bottom:.5rem}
  .chip{display:inline-flex;align-items:center;gap:.5rem;font-size:.75rem;font-weight:500;padding:.375rem .75rem;border-radius:.75rem;border:1px solid}
  .chip.sano{background:#dcfce7;color:#166534;border-color:#bbf7d0}
  .chip.ausente{background:#f1f5f9;color:#475569;border-color:#e2e8f0}
  .chip.caries{background:#fecaca;color:#991b1b;border-color:#fca5a5}
  .chip.obturado{background:#fef3c7;color:#92400e;border-color:#fcd34d}
  .chip.sellado{background:#dbeafe;color:#1e40af;border-color:#93c5fd}

  .mini-grid{display:grid;grid-template-columns:repeat(5, 20px);gap:6px;align-items:center;margin:1rem 0}
  .mini-cell{width:20px;height:20px;border-radius:4px;border:2px solid #cbd5e1;background:#f8fafc;transition:all .2s ease}
  .mini-cell.active{border-color:#3b82f6;background:#dbeafe}
  .mini-O{grid-column:3}.mini-M{grid-column:1}.mini-D{grid-column:5}.mini-B{grid-column:2}.mini-L{grid-column:4}

  .toast{position:fixed;top:20px;right:20px;background:#1f2937;color:#fff;padding:.75rem 1rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.2);z-index:50;font-weight:500;transform:translateX(100%);animation:slideIn .3s ease forwards}
  @keyframes slideIn{to{transform:translateX(0)}}
</style>

<div class="max-w-7xl mx-auto">
  {{-- Header informativo --}}
  <div class="panel bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200 mb-6" style="border-color:#bfdbfe;">
    <div class="flex items-center gap-4">
      <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
      </div>
      <div>
        <h1 class="text-xl font-bold text-slate-800">Odontograma Dental</h1>
        <p class="text-sm text-slate-600 mt-1">Paciente: <span class="font-semibold">{{ $patient->last_name }}, {{ $patient->first_name }}</span> @if($patient->ci) • CI: {{ $patient->ci }} @endif @if(isset($age)) • {{ $age }} años @endif</p>
      </div>
    </div>
  </div>

  <div class="grid lg:grid-cols-4 gap-6">
    {{-- Panel principal --}}
    <section class="panel lg:col-span-3">
      {{-- Toolbar --}}
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="toolbar-section">
          <label>Modo de selección</label>
          <select id="mode" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"><option value="single">Individual</option><option value="multi">Múltiple</option></select>
        </div>
        <div class="toolbar-section">
          <label>Estado de pieza</label>
          <div class="apply-group" style="display:flex;gap:.5rem;align-items:end;">
            <div class="select-flex" style="flex:1 1 auto;">
              <select id="statePiece" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"><option value="">Seleccionar estado</option><option value="sano">Sano</option><option value="ausente">Ausente</option></select>
            </div>
            <button id="applyPiece" class="btn-compact secondary">Aplicar</button>
          </div>
        </div>
        <div class="toolbar-section">
          <label>Superficie dental</label>
          <select id="surface" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"><option value="">Seleccionar superficie</option><option value="O">Oclusal/Incisal</option><option value="M">Mesial</option><option value="D">Distal</option><option value="B">Vestibular/Bucal</option><option value="L">Lingual/Palatina</option></select>
        </div>
        <div class="toolbar-section">
          <label>Condición</label>
          <div class="apply-group" style="display:flex;gap:.5rem;align-items:end;">
            <div class="select-flex" style="flex:1 1 auto;">
              <select id="condition" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"><option value="">Seleccionar condición</option><option value="caries">Caries</option><option value="obturado">Obturado</option><option value="sellado">Sellado</option></select>
            </div>
            <button id="applySurface" class="btn-compact secondary">Aplicar</button>
          </div>
        </div>
      </div>

      {{-- ===== Superior (línea recta) ===== --}}
      <div class="mb-10">
        <div class="section-title flex items-center gap-2"><svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>Arcada Superior</div>
        <div class="row-odontograma">
          <div class="baseline"></div><div class="midline"></div>
          <div class="teeth-row sup">
            @foreach($upperCodes as $code)
              @php $toothStatus = optional($teethByCode->get($code))->status ?? ''; $toothClass = $toothStatus ? "tooth-{$toothStatus}" : ''; $shape = getToothSVG($code); @endphp
              <button type="button" class="tooth-arcada {{ $toothClass }}" data-code="{{ $code }}" data-status="{{ $toothStatus }}">
                @include('admin.odontograms.partials.teeth.' . $shape)
                <span class="badge-dot"></span>
                <span class="tooth-number">{{ $code }}</span>
              </button>
            @endforeach
          </div>
        </div>
      </div>

      {{-- ===== Inferior (línea recta) ===== --}}
      <div class="mb-6">
        <div class="section-title flex items-center gap-2"><svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>Arcada Inferior</div>
        <div class="row-odontograma">
          <div class="baseline"></div><div class="midline"></div>
          <div class="teeth-row inf">
            @foreach($lowerCodes as $code)
              @php $toothStatus = optional($teethByCode->get($code))->status ?? ''; $toothClass = $toothStatus ? "tooth-{$toothStatus}" : ''; $shape = getToothSVG($code); @endphp
              <button type="button" class="tooth-arcada {{ $toothClass }}" data-code="{{ $code }}" data-status="{{ $toothStatus }}">
                @include('admin.odontograms.partials.teeth.' . $shape)
                <span class="badge-dot"></span>
                <span class="tooth-number">{{ $code }}</span>
              </button>
            @endforeach
          </div>
        </div>
      </div>

      <div class="flex gap-3 pt-6 mt-6 border-t border-slate-200">
        <button id="clearSel" type="button" class="btn-compact secondary flex-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Limpiar selección</button>
        <button id="saveAll" type="button" class="btn-compact primary flex-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Guardar odontograma</button>
      </div>
    </section>

    {{-- Panel lateral --}}
    <aside class="panel">
      <div class="mb-6"><h4 class="section-title flex items-center gap-2"><svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Leyenda de estados</h4><ul class="space-y-2"><li><span class="chip sano">Sano</span></li><li><span class="chip caries">Caries</span></li><li><span class="chip obturado">Obturado</span></li><li><span class="chip sellado">Sellado</span></li><li><span class="chip ausente">Ausente</span></li></ul></div>
      <div class="border-t my-4"></div>
      <div class="mb-6"><h4 class="section-title flex items-center gap-2"><svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>Pieza seleccionada</h4><div id="selHeader" class="text-sm text-slate-500 mb-3">Ninguna pieza seleccionada</div><div id="selStatus" class="mb-3"></div><div id="selMini" class="mini-grid mb-3" style="display:none"><div class="mini-cell mini-M" title="Mesial"></div><div class="mini-cell mini-B" title="Vestibular/Bucal"></div><div class="mini-cell mini-O" title="Oclusal/Incisal"></div><div class="mini-cell mini-L" title="Lingual/Palatina"></div><div class="mini-cell mini-D" title="Distal"></div></div><ul id="selSurfaces" class="text-sm space-y-2"></ul></div>
      <div class="border-t my-4"></div>
      <div class="mb-4"><h4 class="section-title flex items-center gap-2"><svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Notas clínicas</h4><textarea id="note" rows="4" class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none" placeholder="Registre observaciones específicas para la(s) pieza(s) seleccionada(s)..."></textarea><button id="saveNote" type="button" class="btn-compact secondary w-full mt-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Guardar nota</button></div>
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-3"><p class="text-xs text-blue-700"><strong>Tip:</strong> Use el modo "Múltiple" para aplicar la misma condición a varias piezas simultáneamente.</p></div>
    </aside>
  </div>
</div>

{{-- Estado inicial --}}
<script>
  const APPT_ID = {{ request('appointment_id') ? (int)request('appointment_id') : 'null' }};
  const TEETH_INIT = {!! json_encode($teethInit, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!};
  function applyStateToTooth(code, state){
    const el = document.querySelector(`.tooth-arcada[data-code="${code}"]`); if(!el) return;
    el.classList.remove('tooth-sano','tooth-ausente'); el.dataset.status = state.status || '';
    const dot = el.querySelector('.badge-dot'); const svg = el.querySelector('svg');
    if(state.status==='ausente'){ el.classList.add('tooth-ausente'); dot.style.display='none'; if(svg) svg.style.opacity='0.3'; }
    else if(state.status==='sano'){ el.classList.add('tooth-sano'); dot.style.display='block'; if(svg) svg.style.opacity='1'; }
    else { dot.style.display='none'; if(svg) svg.style.opacity='1'; }
    ['O','M','D','B','L'].forEach(s=>{ const seg=el.querySelector(`.s-${s}`); seg&&seg.classList.remove('cond-caries','cond-obturado','cond-sellado'); });
    (state.surfaces||[]).forEach(s=>{ const seg=el.querySelector(`.s-${s.surface}`); seg&&seg.classList.add('cond-'+s.condition); });
  }
  document.addEventListener('DOMContentLoaded',()=>{ Object.entries(TEETH_INIT||{}).forEach(([code,state])=>applyStateToTooth(code,state)); });
</script>

<script>
(()=>{
  const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const MODE = document.getElementById('mode');
  const STATE_PIECE = document.getElementById('statePiece');
  const APPLY_P = document.getElementById('applyPiece');
  const SURFACE = document.getElementById('surface');
  const CONDITION = document.getElementById('condition');
  const APPLY_S = document.getElementById('applySurface');
  const CLEAR = document.getElementById('clearSel');
  const SAVE_ALL = document.getElementById('saveAll');
  const SAVE_NOTE = document.getElementById('saveNote');
  const NOTE = document.getElementById('note');

  const $teeth = Array.from(document.querySelectorAll('.tooth-arcada'));
  const selected = new Set();
  const changes = new Map();
  const SURF_LABELS = { O:'Oclusal/Incisal', M:'Mesial', D:'Distal', B:'Vestibular/Bucal', L:'Lingual/Palatina' };
  const SEL_HEADER = document.getElementById('selHeader');
  const SEL_STATUS = document.getElementById('selStatus');
  const SEL_MINI = document.getElementById('selMini');
  const SEL_SURFACES = document.getElementById('selSurfaces');

  function readState(code){
    const el = document.querySelector(`.tooth-arcada[data-code="${code}"]`);
    const state = { status: el?.dataset?.status || null, surfaces: {} };
    ['O','M','D','B','L'].forEach(s=>{ const seg = el?.querySelector(`.s-${s}`); if(!seg) return; if(seg.classList.contains('cond-caries')) state.surfaces[s]='caries'; if(seg.classList.contains('cond-obturado')) state.surfaces[s]='obturado'; if(seg.classList.contains('cond-sellado')) state.surfaces[s]='sellado'; });
    const pending = changes.get(code); if(pending){ if(pending.status!==undefined) state.status=pending.status; if(pending.surfaces) state.surfaces={...state.surfaces,...pending.surfaces}; }
    return state;
  }

  function renderSelectionPanel(){
    if(selected.size===0){ SEL_HEADER.textContent='Ninguna pieza seleccionada'; SEL_STATUS.innerHTML=''; SEL_MINI.style.display='none'; SEL_SURFACES.innerHTML=''; return; }
    if(selected.size>1){ SEL_HEADER.textContent=`${selected.size} piezas seleccionadas`; SEL_STATUS.innerHTML=''; SEL_MINI.style.display='none'; SEL_SURFACES.innerHTML=''; return; }
    const code=[...selected][0]; const st=readState(code); SEL_HEADER.textContent=`Pieza ${code}`; const statusChip = st.status?`<span class="chip ${st.status}">${st.status==='sano'?'Sano':'Ausente'}</span>`:''; SEL_STATUS.innerHTML=statusChip;
    const items = Object.entries(st.surfaces).map(([s,cond])=>`<li class="flex items-center gap-2"><span class="chip ${cond}">${cond}</span><span class="text-slate-600 text-sm">${SURF_LABELS[s]}</span></li>`);
    if(items.length){ SEL_MINI.style.display=''; SEL_MINI.querySelectorAll('.mini-cell').forEach(c=>{c.classList.remove('active'); c.style.background=''}); Object.entries(st.surfaces).forEach(([s,cond])=>{ const cell=SEL_MINI.querySelector(`.mini-${s}`); if(!cell) return; cell.classList.add('active'); cell.style.background=({caries:'#fecaca',obturado:'#fde68a',sellado:'#bae6fd'})[cond]||''; }); SEL_SURFACES.innerHTML=items.join(''); }
    else { SEL_MINI.style.display='none'; SEL_SURFACES.innerHTML='<li class="text-slate-500 text-sm">Sin hallazgos en superficies</li>'; }
  }

  const setSelected = ($el, on) => { $el.classList.toggle('selected', on); const code=$el.dataset.code; on?selected.add(code):selected.delete(code); };
  const ensureItem = (code) => { if(!changes.has(code)){ const el=document.querySelector(`.tooth-arcada[data-code="${code}"]`); changes.set(code,{ status: el?.dataset?.status || null, notes:null, surfaces:{} }); } return changes.get(code); };
  const setBadgeFor = (code, status) => { const el=document.querySelector(`.tooth-arcada[data-code="${code}"]`); if(!el) return; const dot=el.querySelector('.badge-dot'); const svg=el.querySelector('svg'); el.classList.remove('tooth-sano','tooth-ausente'); if(status==='ausente'){ el.classList.add('tooth-ausente'); dot.style.display='none'; svg&&(svg.style.opacity='0.3'); } else if(status==='sano'){ el.classList.add('tooth-sano'); dot.style.display='block'; svg&&(svg.style.opacity='1'); } else { dot.style.display='none'; svg&&(svg.style.opacity='1'); } };
  const paintSurface = (code, s, cond) => { const el=document.querySelector(`.tooth-arcada[data-code="${code}"] .s-${s}`); if(!el) return; el.classList.remove('cond-caries','cond-obturado','cond-sellado'); if(cond) el.classList.add('cond-'+cond); };
  const paintAllSurfaces = (code) => { ['O','M','D','B','L'].forEach(s=>paintSurface(code,s,null)); };

  $teeth.forEach($t=>{ $t.addEventListener('click',()=>{ if(MODE.value==='single'){ $teeth.forEach(el=>setSelected(el,false)); } setSelected($t,!$t.classList.contains('selected')); renderSelectionPanel(); }); });

  APPLY_P.addEventListener('click',()=>{ if(!selected.size) return toast('Seleccione al menos una pieza'); const val=STATE_PIECE.value||null; if(!val) return toast('Seleccione un estado'); [...selected].forEach(code=>{ const it=ensureItem(code); it.status=val; const el=document.querySelector(`.tooth-arcada[data-code="${code}"]`); el.dataset.status=val||''; setBadgeFor(code,val); if(val==='ausente'){ it.surfaces={}; paintAllSurfaces(code); } }); renderSelectionPanel(); STATE_PIECE.value=''; });

  APPLY_S.addEventListener('click',()=>{ if(!selected.size) return toast('Seleccione al menos una pieza'); const s=SURFACE.value, c=CONDITION.value; if(!s||!c) return toast('Seleccione superficie y condición'); [...selected].forEach(code=>{ const it=ensureItem(code); if(it.status==='ausente') it.status='sano'; paintSurface(code,s,c); it.surfaces[s]=c; const el=document.querySelector(`.tooth-arcada[data-code="${code}"]`); el.dataset.status=it.status||''; setBadgeFor(code,it.status); }); renderSelectionPanel(); SURFACE.value=''; CONDITION.value=''; });

  CLEAR.addEventListener('click',()=>{ if(!selected.size) return toast('Seleccione al menos una pieza'); [...selected].forEach(code=>{ const it=ensureItem(code); it.status=null; it.surfaces={}; const el=document.querySelector(`.tooth-arcada[data-code="${code}"]`); el.dataset.status=''; setBadgeFor(code,null); paintAllSurfaces(code); }); selected.clear(); renderSelectionPanel(); });

  SAVE_NOTE.addEventListener('click',()=>{ const text=NOTE.value.trim(); if(!selected.size) return toast('Seleccione una pieza'); if(!text) return toast('Ingrese una nota'); [...selected].forEach(code=>ensureItem(code).notes=text); NOTE.value=''; toast('Nota aplicada a la selección'); });

  SAVE_ALL.addEventListener('click',async()=>{ if(changes.size===0) return toast('No hay cambios para guardar'); const items=Array.from(changes.entries()).map(([code,it])=>({ tooth_code:code, status:it.status||null, notes:it.notes||null, surfaces:Object.entries(it.surfaces||{}).map(([surface,condition])=>({surface,condition})) })); try{ const res=await fetch("{{ route('admin.odontograms.teeth.upsert',$odontogram) }}",{ method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}, body:JSON.stringify({ items, appointment_id: APPT_ID })}); if(!res.ok) throw new Error('HTTP '+res.status); const data=await res.json(); changes.clear(); toast('Odontograma guardado correctamente ✅'); if(data.redirect){ setTimeout(()=>location.href=data.redirect,1500); } }catch(e){ console.error(e); toast('Error al guardar el odontograma'); } });

  function toast(msg){ const t=document.createElement('div'); t.className='toast'; t.textContent=msg; document.body.appendChild(t); setTimeout(()=>{ t.style.opacity='0'; t.style.transform='translateX(100%)'; setTimeout(()=>t.remove(),300); },3000); }

  renderSelectionPanel();
})();
</script>
@endsection