@extends('layouts.app')
@section('title','Horarios · ' . $dentist->name)

@section('header-actions')
  <a href="{{ route('admin.schedules') }}" class="btn btn-ghost">Volver</a>
@endsection

@section('content')
  <form method="post" action="{{ route('admin.schedules.update',$dentist) }}" class="space-y-4" id="sched-form">
    @csrf

    <div class="card">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="font-semibold">{{ $dentist->name }}</h3>
          <p class="text-xs text-slate-500">{{ $dentist->specialty ?: 'Sin especialidad' }}</p>
        </div>
        <div class="text-xs text-slate-500">Dom=0 … Sáb=6</div>
      </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
      @foreach(range(0,6) as $day)
        @php
          $label  = $dayLabels[$day];
          $blocks = $byDay[$day] ?? collect();
        @endphp
        <section class="card" data-day="{{ $day }}">
          <div class="flex items-center justify-between mb-2">
            <h4 class="font-semibold">{{ $label }} <span class="text-xs text-slate-500">(día {{ $day }})</span></h4>
            <button type="button" class="btn btn-ghost add-block">+ Bloque</button>
          </div>

          <div class="space-y-3 blocks">
            @forelse($blocks as $i => $b)
              <div class="border rounded p-3 block-row" data-index="{{ $i }}">
                <div class="grid gap-3 md:grid-cols-4">
                  <div>
                    <label class="block text-xs text-slate-500 mb-1">Inicio</label>
                    <input type="time" step="60"
                           name="schedule[{{ $day }}][{{ $i }}][start_time]"
                           value="{{ \Illuminate\Support\Str::substr($b->start_time,0,5) }}"
                           class="w-full border rounded px-3 py-2">
                  </div>
                  <div>
                    <label class="block text-xs text-slate-500 mb-1">Fin</label>
                    <input type="time" step="60"
                           name="schedule[{{ $day }}][{{ $i }}][end_time]"
                           value="{{ \Illuminate\Support\Str::substr($b->end_time,0,5) }}"
                           class="w-full border rounded px-3 py-2">
                  </div>
                  <div>
  <label class="block text-xs text-slate-500 mb-1">Silla</label>
  @php
    $key = $day.':'.$i;
    $allowed = $availMap[$key] ?? $chairs->pluck('id')->all();
    $selectedChair = $b->chair_id ?? '';
  @endphp
  <select
    name="schedule[{{ $day }}][{{ $i }}][chair_id]"
    class="w-full border rounded px-3 py-2 chair-select"
    data-day="{{ $day }}"
  >
    <option value="">— Sin silla —</option>
    @foreach($chairs as $c)
      <option value="{{ $c->id }}"
        @selected($selectedChair == $c->id)
        @disabled(!in_array($c->id, $allowed))
      >
        {{ $c->name }} ({{ $c->shift }})
      </option>
    @endforeach
  </select>
  <small class="text-xs text-slate-500 chair-hint">Elige inicio y fin para validar disponibilidad.</small>
</div>
                  <div class="flex items-end">
                    <button type="button" class="btn btn-danger remove-block w-full md:w-auto">Eliminar</button>
                  </div>
                </div>

                <div class="mt-2">
                  <label class="block text-xs text-slate-500 mb-1">Pausas (ej.: 12:00-12:30,15:00-15:15)</label>
                  <input
                    name="schedule[{{ $day }}][{{ $i }}][breaks]"
                    value="{{ collect($b->breaks ?? [])->map(fn($x)=>($x['start']??'').'-'.($x['end']??''))->implode(',') }}"
                    class="w-full border rounded px-3 py-2"
                    placeholder="12:00-12:30,15:00-15:15">
                </div>
              </div>
            @empty
              <p class="text-sm text-slate-500 empty-hint">Sin bloques. Usa “+ Bloque”.</p>
            @endforelse
          </div>
        </section>
      @endforeach
    </div>

    <div class="flex items-center gap-2">
      <button class="btn btn-primary">Guardar horarios</button>
      <span class="text-xs text-slate-500">Se reemplazarán los bloques existentes de este odontólogo.</span>
    </div>
  </form>

  <template id="tpl-block">
  <div class="border rounded p-3 block-row" data-index="__IDX__">
    <div class="grid gap-3 md:grid-cols-4">
      <div>
        <label class="block text-xs text-slate-500 mb-1">Inicio</label>
        <input type="time" step="60" name="__START__" class="w-full border rounded px-3 py-2 time-start">
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Fin</label>
        <input type="time" step="60" name="__END__" class="w-full border rounded px-3 py-2 time-end">
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Silla</label>
        <select name="__CHAIR__" class="w-full border rounded px-3 py-2 chair-select" data-day="__DAY__">
          <option value="">— Sin silla —</option>
          @foreach($chairs as $c)
            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->shift }})</option>
          @endforeach
        </select>
        <small class="text-xs text-slate-500 chair-hint">Elige inicio y fin para validar disponibilidad.</small>
      </div>
      <div class="flex items-end">
        <button type="button" class="btn btn-danger remove-block w-full md:w-auto">Eliminar bloque</button>
      </div>
    </div>
    <div class="mt-2">
      <label class="block text-xs text-slate-500 mb-1">Pausas (ej.: 12:00-12:30,15:00-15:15)</label>
      <input name="__BREAKS__" class="w-full border rounded px-3 py-2" placeholder="12:00-12:30,15:00-15:15">
    </div>
  </div>
</template>

  <script>
document.addEventListener('DOMContentLoaded', () => {
  // 1) Tomamos la plantilla del <template id="tpl-block">
  const tplEl = document.getElementById('tpl-block');
  const tpl   = tplEl ? tplEl.innerHTML : '';
  if (!tpl) {
    console.error('[schedules] No se encontró #tpl-block o está vacío');
    return;
  }

  // 2) Para cada día, cableamos el botón +Bloque y el borrado
  document.querySelectorAll('section.card[data-day]').forEach(section => {
    const day       = section.getAttribute('data-day');
    const addBtn    = section.querySelector('.add-block');
    const container = section.querySelector('.blocks');

    const ensureHintGone = () => {
      const hint = container.querySelector('.empty-hint');
      if (hint) hint.remove();
    };

    // Agregar nuevo bloque
    addBtn?.addEventListener('click', () => {
      ensureHintGone();
      const idx = container.querySelectorAll('.block-row').length;

      // Construimos el HTML del nuevo bloque
      let html = tpl.replaceAll('__IDX__', idx);
      html = html
        .replace('__START__',  `schedule[${day}][${idx}][start_time]`)
        .replace('__END__',    `schedule[${day}][${idx}][end_time]`)
        .replace('__BREAKS__', `schedule[${day}][${idx}][breaks]`)
        .replace('__CHAIR__',  `schedule[${day}][${idx}][chair_id]`)
        .replace('__DAY__',     day);

      const div = document.createElement('div');
      div.innerHTML = html.trim();
      const node = div.firstChild;
      container.appendChild(node);

      // Autovalidación de sillas para el nuevo bloque
      wireChairAuto(node, day);
    });

    // Cablea los bloques existentes al cargar
    container.querySelectorAll('.block-row').forEach((row) => wireChairAuto(row, day));

    // Eliminar bloque
    container.addEventListener('click', (e) => {
      if (e.target.closest('.remove-block')) {
        e.target.closest('.block-row').remove();
        if (!container.querySelector('.block-row')) {
          const p = document.createElement('p');
          p.className = 'text-sm text-slate-500 empty-hint';
          p.textContent = 'Sin bloques. Usa “+ Bloque”.';
          container.appendChild(p);
        }
      }
    });
  });

  // 3) Normaliza guiones/espacios en "pausas"
  const form = document.getElementById('sched-form');
  form?.addEventListener('input', (e) => {
    const el = e.target;
    if (el?.name && /\[breaks\]$/.test(el.name)) {
      el.value = el.value.replace(/[–—−]/g, '-').replace(/\s+/g, '');
    }
  });

  // --- helper: autovalida sillas al cambiar inicio/fin ---
  function wireChairAuto(row, day) {
    const start = row.querySelector('input[type="time"][name$="[start_time]"]');
    const end   = row.querySelector('input[type="time"][name$="[end_time]"]');
    const sel   = row.querySelector('select.chair-select');
    const hint  = row.querySelector('.chair-hint');

    function enableAll() {
      Array.from(sel.options).forEach(o => { if (o.value) o.disabled = false; });
    }

    async function refreshChairs(){
      const s = start?.value || '';
      const e = end?.value || '';
      if (!sel) return;

      if (!s || !e) {
        enableAll();
        if (hint) hint.textContent = 'Elige inicio y fin para validar disponibilidad.';
        return;
      }

      const s5 = s.slice(0,5);
      const e5 = e.slice(0,5);
      const url = `{{ route('admin.schedules.chairs.options',$dentist) }}?day=${encodeURIComponent(day)}&start_time=${encodeURIComponent(s5)}&end_time=${encodeURIComponent(e5)}`;

      try {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
        if (!res.ok) throw new Error('HTTP '+res.status);
        const list = await res.json();
        const byId = new Map(list.map(x => [String(x.id), x]));

        enableAll();
        Array.from(sel.options).forEach(opt => {
          if (!opt.value) return; // “Sin silla”
          const info = byId.get(opt.value);
          opt.disabled = info ? !info.available : false;
        });

        const totalAvail = list.filter(x => x.available).length;
        if (hint) hint.textContent = totalAvail
          ? `Sillas disponibles: ${totalAvail}`
          : 'Sin sillas disponibles en ese rango.';

        if (sel.value && sel.selectedOptions[0]?.disabled) sel.value = '';
      } catch {
        enableAll();
        if (hint) hint.textContent = 'No se pudo validar sillas.';
      }
    }

    ['change','input'].forEach(evt => {
      start?.addEventListener(evt, refreshChairs);
      end  ?.addEventListener(evt, refreshChairs);
    });

    if (start?.value && end?.value) refreshChairs();
  }
});
</script>
@endsection