{{-- === ESTILOS LOCALES PARA EL TABLERO === --}}
<style>
  .tooth-tile{
    width:64px;height:72px;border:1px solid #e2e8f0;border-radius:.5rem;
    background:#fff;display:flex;align-items:flex-end;justify-content:center;
    position:relative;transition:box-shadow .15s, transform .05s;
  }
  .tooth-tile:hover{ box-shadow:0 1px 6px rgba(0,0,0,.07); }
  .tooth-tile.selected{ outline:2px solid #2563eb; outline-offset:2px; }
  .tooth-code{
    position:absolute;top:4px;left:6px;font-size:.65rem;color:#64748b;
    font-variant-numeric:tabular-nums;
  }
  .tooth-fill{       /* color de estado de la pieza */
    position:absolute;inset:0;border-radius:.5rem;opacity:.22;pointer-events:none;
  }
  .t-sano      { background:#10b981; }
  .t-caries    { background:#ef4444; }
  .t-obturado  { background:#f59e0b; }
  .t-ausente   { background:#94a3b8; }

  .surfaces{position:absolute;top:22px;left:4px;right:4px;display:flex;flex-wrap:wrap;gap:2px;justify-content:center}
  .surf-chip{font-size:.6rem;line-height:1;padding:.15rem .25rem;border-radius:.25rem;border:1px solid #e5e7eb;background:#fff}
  .surf-O{ background:#f1f5f9 }      /* occlusal/incisal */
  .surf-M{ background:#fee2e2 }      /* mesial  */
  .surf-D{ background:#fee2e2 }      /* distal  */
  .surf-B{ background:#e0f2fe }      /* bucal   */
  .surf-L{ background:#ede9fe }      /* lingual */
</style>

<div class="grid gap-6 lg:grid-cols-12">
  {{-- TABLERO --}}
  <section class="lg:col-span-9 card">
    <div class="mb-3 text-sm text-slate-600">Arcada superior</div>

    {{-- Fila: 18..11 --}}
    <div class="grid grid-cols-8 gap-2 mb-2">
      @foreach([18,17,16,15,14,13,12,11] as $code)
        <button class="tooth-tile" data-code="{{ $code }}">
          <span class="tooth-code">{{ $code }}</span>
          <div class="tooth-fill"></div>
          <div class="surfaces"></div>
        </button>
      @endforeach
    </div>

    {{-- Fila: 21..28 --}}
    <div class="grid grid-cols-8 gap-2 mb-6">
      @foreach([21,22,23,24,25,26,27,28] as $code)
        <button class="tooth-tile" data-code="{{ $code }}">
          <span class="tooth-code">{{ $code }}</span>
          <div class="tooth-fill"></div>
          <div class="surfaces"></div>
        </button>
      @endforeach
    </div>

    <div class="mb-3 text-sm text-slate-600">Arcada inferior</div>

    {{-- Fila: 48..41 --}}
    <div class="grid grid-cols-8 gap-2 mb-2">
      @foreach([48,47,46,45,44,43,42,41] as $code)
        <button class="tooth-tile" data-code="{{ $code }}">
          <span class="tooth-code">{{ $code }}</span>
          <div class="tooth-fill"></div>
          <div class="surfaces"></div>
        </button>
      @endforeach
    </div>

    {{-- Fila: 31..38 --}}
    <div class="grid grid-cols-8 gap-2">
      @foreach([31,32,33,34,35,36,37,38] as $code)
        <button class="tooth-tile" data-code="{{ $code }}">
          <span class="tooth-code">{{ $code }}</span>
          <div class="tooth-fill"></div>
          <div class="surfaces"></div>
        </button>
      @endforeach
    </div>
  </section>

  {{-- LATERAL (LEYENDA / SELECCIÓN) --}}
  <aside class="lg:col-span-3 card">
    <h4 class="font-semibold mb-2">Leyenda</h4>
    <div class="flex flex-wrap gap-2 text-xs">
      <span class="px-2 py-1 rounded bg-emerald-100 text-emerald-800">Sano</span>
      <span class="px-2 py-1 rounded bg-rose-100 text-rose-800">Caries</span>
      <span class="px-2 py-1 rounded bg-amber-100 text-amber-800">Obturado</span>
      <span class="px-2 py-1 rounded bg-slate-200 text-slate-700">Ausente</span>
    </div>
    <div class="mt-4 text-sm text-slate-600" id="selected-label">Selecciona una pieza…</div>
  </aside>
</div>

{{-- ====== Datos desde PHP (construidos en el controlador) ====== --}}
<script>
  const CSRF   = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const ODO_ID = {{ $odontogram->id }};
  const TEETH  = @json($teethPayload); // { "11": {status:'sano', notes:'', surfaces:[{surface:'O',condition:'caries'}...]}, ... }
</script>

<script>
  (function () {
    const tiles = document.querySelectorAll('.tooth-tile');
    const selected = new Set();

    // Pinta estado + surfaces iniciales
    tiles.forEach(t => {
      const code = t.dataset.code;
      const data = TEETH[code];
      if (!data) return;

      // estado de pieza
      if (data.status) {
        const fill = t.querySelector('.tooth-fill');
        fill.classList.add('t-'+data.status); // t-sano, t-caries, etc.
      }
      // surfaces como chips
      if (data.surfaces && data.surfaces.length) {
        const box = t.querySelector('.surfaces');
        data.surfaces.forEach(s => {
          const chip = document.createElement('span');
          chip.className = 'surf-chip surf-'+s.surface;
          chip.textContent = s.surface;       // O, M, D, B, L
          chip.title = s.condition || '';
          box.appendChild(chip);
        });
      }
    });

    // Selección visual
    tiles.forEach(t => {
      t.addEventListener('click', () => {
        const code = t.dataset.code;
        if (selected.has(code)) {
          selected.delete(code);
          t.classList.remove('selected');
        } else {
          // selección individual (si quieres múltiple, quita este bloque)
          selected.forEach(c => document.querySelector(`.tooth-tile[data-code="${c}"]`)?.classList.remove('selected'));
          selected.clear();

          selected.add(code);
          t.classList.add('selected');
          document.getElementById('selected-label').textContent = 'Pieza seleccionada: '+code;
        }
      });
    });

    // Aquí puedes enganchar tus botones de “Aplicar estado”, “Limpiar”, etc.
    // y hacer fetch POST a tus endpoints existentes para guardar cambios.
    // Ejemplo (esqueleto):
    async function applyStatusToSelection(status) {
      if (selected.size === 0) return;
      const codes = Array.from(selected);
      // UI inmediata:
      codes.forEach(c => {
        const tile = document.querySelector(`.tooth-tile[data-code="${c}"]`);
        const fill = tile.querySelector('.tooth-fill');
        fill.className = 'tooth-fill t-'+status;
      });
      // TODO: POST al backend
      // await fetch(`/admin/odontograms/${ODO_ID}/teeth`, { ... })
    }
    window.applyStatusToSelection = applyStatusToSelection;
  })();
</script>
