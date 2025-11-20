@extends('layouts.app')
@section('title', 'Historia Clínica - ' . $patient->full_name)

@section('header-actions')
  <a href="{{ route('admin.patients.show',$patient) }}"
     class="btn btn-ghost flex items-center gap-2 border border-blue-200 text-blue-700 hover:bg-blue-50 hover:text-blue-800">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver al Perfil
  </a>
@endsection

@section('content')
  <div class="max-w-4xl mx-auto">
    {{-- Header informativo --}}
    <div class="card bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200 mb-6">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-bold text-slate-800">Historia Clínica Completa</h1>
          <p class="text-sm text-slate-600 mt-1">
            Paciente: <span class="font-semibold">{{ $patient->last_name }}, {{ $patient->first_name }}</span>
            @if($patient->ci) • CI: {{ $patient->ci }} @endif
            @if(isset($age)) • {{ $age }} años @endif
          </p>
        </div>
      </div>
    </div>

    {{-- Filtros rápidos --}}
    <div class="card mb-6">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold text-slate-700 flex items-center gap-2">
          <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
          </svg>
          Filtros
        </h3>
        <div id="events-counter" class="text-sm text-slate-500">
          {{ $events->count() }} evento(s) registrado(s)
        </div>
      </div>

      <div class="flex flex-wrap gap-2 mt-3">
        <button type="button" data-filter="all" class="filter-btn active btn btn-ghost text-xs bg-blue-100 text-blue-700 border-blue-200">Todos</button>
        <button type="button" data-filter="appointment" class="filter-btn btn btn-ghost text-xs border border-slate-300 text-slate-600">Citas</button>
        <button type="button" data-filter="note" class="filter-btn btn btn-ghost text-xs border border-slate-300 text-slate-600">Notas</button>
        <button type="button" data-filter="diagnosis" class="filter-btn btn btn-ghost text-xs border border-slate-300 text-slate-600">Diagnósticos</button>
        <button type="button" data-filter="treatment" class="filter-btn btn btn-ghost text-xs border border-slate-300 text-slate-600">Tratamientos</button>
        <button type="button" data-filter="odontogram" class="filter-btn btn btn-ghost text-xs border border-slate-300 text-slate-600">Odontogramas</button>
        <button type="button" data-filter="consent" class="filter-btn btn btn-ghost text-xs border border-slate-300 text-slate-600">Consentimientos</button>
        <button type="button" data-filter="attachment" class="filter-btn btn btn-ghost text-xs border border-slate-300 text-slate-600">Archivos</button>
      </div>
    </div>

    {{-- Timeline de eventos --}}
    <div class="card">
      @if($events->isEmpty())
        <div class="text-center py-12 text-slate-500">
          <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <p class="font-medium text-slate-600 text-lg mb-2">No hay eventos clínicos registrados</p>
          <p class="text-sm text-slate-500">La historia clínica de este paciente está vacía.</p>
        </div>
      @else
        <div class="relative">
          {{-- Línea vertical del timeline --}}
          <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-slate-200"></div>

          <ul class="space-y-6" id="timeline-list">
            @foreach($events as $e)
              @php
                $typeConfig = match($e['type'] ?? '') {
                  'appointment' => [
                    'color' => 'blue',
                    'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                    'badge' => 'bg-blue-100 text-blue-800 border border-blue-200',
                    'label' => 'Cita',
                  ],
                  'note' => [
                    'color' => 'amber',
                    'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                    'badge' => 'bg-amber-100 text-amber-800 border border-amber-200',
                    'label' => 'Nota',
                  ],
                  'diagnosis' => [
                    'color' => 'rose',
                    'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    'badge' => 'bg-rose-100 text-rose-800 border border-rose-200',
                    'label' => 'Diagnóstico',
                  ],
                  'treatment' => [
                    'color' => 'emerald',
                    'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                    'badge' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                    'label' => 'Tratamiento',
                  ],
                  'odontogram' => [
                    'color' => 'sky',
                    'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01',
                    'badge' => 'bg-sky-100 text-sky-800 border border-sky-200',
                    'label' => 'Odontograma',
                  ],
                  'consent' => [
                    'color' => 'purple',
                    'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    'badge' => 'bg-purple-100 text-purple-800 border border-purple-200',
                    'label' => 'Consentimiento',
                  ],
                  'attachment' => [
                    'color' => 'slate',
                    'icon' => 'M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13',
                    'badge' => 'bg-slate-100 text-slate-800 border border-slate-300',
                    'label' => 'Archivo',
                  ],
                  'payment' => [
                    'color' => 'teal',
                    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                    'badge' => 'bg-teal-100 text-teal-800 border border-teal-200',
                    'label' => 'Pago',
                  ],
                  default => [
                    'color' => 'slate',
                    'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'badge' => 'bg-slate-100 text-slate-800 border border-slate-300',
                    'label' => 'Evento',
                  ],
                };

                $ts = $e['ts'] instanceof \Illuminate\Support\Carbon ? $e['ts'] : \Illuminate\Support\Carbon::parse($e['ts'] ?? now());
                $url = $e['url'] ?? '#';
                $isRecent = $ts->diffInDays(now()) <= 7;
              @endphp

              <li class="relative pl-16 timeline-item" data-type="{{ $e['type'] ?? 'other' }}">
                {{-- Punto del timeline --}}
                <div class="absolute left-6 top-2 -ml-3">
                  <div class="w-6 h-6 rounded-full bg-white border-2 border-{{ $typeConfig['color'] }}-500 flex items-center justify-center shadow-sm">
                    <svg class="w-3 h-3 text-{{ $typeConfig['color'] }}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $typeConfig['icon'] }}"/>
                    </svg>
                  </div>
                </div>

                <div class="bg-white rounded-lg border border-slate-200 p-4 hover:shadow-sm transition-shadow">
                  <div class="flex items-start justify-between gap-4 mb-2">
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-medium px-2 py-1 rounded {{ $typeConfig['badge'] }}">
                          {{ $typeConfig['label'] }}
                        </span>
                        @if($isRecent)
                          <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded border border-green-200">
                            Reciente
                          </span>
                        @endif
                      </div>

                      <h4 class="font-semibold text-slate-800 text-lg mb-1">{{ $e['title'] ?? 'Evento' }}</h4>

                      <div class="text-sm text-slate-500 mb-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $ts->format('d/m/Y H:i') }}
                        • {{ $ts->diffForHumans() }}
                      </div>

                      @if(!empty($e['meta']))
                        <div class="text-sm text-slate-700 bg-slate-50 rounded p-3 mt-2">
                          {{ $e['meta'] }}
                        </div>
                      @endif

                      @if(!empty($e['status']))
                        <div class="mt-2">
                          <span class="inline-block text-xs font-medium px-2 py-1 rounded bg-slate-100 text-slate-700 border border-slate-300">
                            Estado: {{ str_replace('_',' ', ucfirst($e['status'])) }}
                          </span>
                        </div>
                      @endif
                    </div>

                    <div class="shrink-0">
                      <a href="{{ $url }}"
                         class="btn btn-ghost flex items-center gap-1 border border-{{ $typeConfig['color'] }}-200 text-{{ $typeConfig['color'] }}-700 hover:bg-{{ $typeConfig['color'] }}-50 hover:text-{{ $typeConfig['color'] }}-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Ver detalles
                      </a>
                    </div>
                  </div>
                </div>
              </li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>
  </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const filterButtons = document.querySelectorAll('.filter-btn');
  const timelineItems = Array.from(document.querySelectorAll('.timeline-item'));
  const counterEl = document.getElementById('events-counter');

  function setActive(btn) {
    filterButtons.forEach(b => {
      b.classList.remove('active', 'bg-blue-100', 'text-blue-700', 'border-blue-200');
      b.classList.add('bg-white', 'text-slate-600', 'border-slate-300');
    });
    btn.classList.add('active', 'bg-blue-100', 'text-blue-700', 'border-blue-200');
    btn.classList.remove('bg-white', 'text-slate-600', 'border-slate-300');
  }

  function applyFilter(filter) {
    let visible = 0;
    timelineItems.forEach(item => {
      const matches = (filter === 'all') || (item.getAttribute('data-type') === filter);
      if (matches) {
        item.classList.remove('hidden');
        visible++;
      } else {
        item.classList.add('hidden');
      }
    });
    if (counterEl) {
      counterEl.textContent = `${visible} evento(s) registrado(s)`;
    }
  }

  filterButtons.forEach(button => {
    button.addEventListener('click', function() {
      const filter = this.getAttribute('data-filter');
      setActive(this);
      applyFilter(filter);
    });
  });
});
</script>

<style>
.timeline-item { transition: all 0.3s ease; }
.filter-btn { transition: all 0.2s ease; }
.filter-btn:hover { transform: translateY(-1px); }
</style>
@endsection
