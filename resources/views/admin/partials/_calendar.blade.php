@php
  $startOfMonth = $month->copy()->startOfMonth();
  $endOfMonth   = $month->copy()->endOfMonth();
  $startOfGrid  = $startOfMonth->copy()->startOfWeek();
  $endOfGrid    = $endOfMonth->copy()->endOfWeek();
  $cursor       = $startOfGrid->copy();
@endphp

<div class="border-b border-slate-200 pb-4 mb-4">
  <h3 class="font-semibold text-slate-800 flex items-center gap-2">
    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    Calendario de Citas
  </h3>
</div>

{{-- Controles del calendario --}}
<div class="flex items-center justify-between mb-6">
  <h4 id="month-label" class="text-lg font-semibold text-slate-800">{{ $month->translatedFormat('F Y') }}</h4>
  <div class="flex gap-2">
    <button 
      class="px-3 py-2 rounded bg-slate-100 hover:bg-slate-200 text-sm transition-colors flex items-center gap-2" 
      data-nav="prev"
      title="Mes anterior"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </button>
    <button 
      class="px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 text-sm transition-colors" 
      data-nav="today"
    >
      Hoy
    </button>
    <button 
      class="px-3 py-2 rounded bg-slate-100 hover:bg-slate-200 text-sm transition-colors flex items-center gap-2" 
      data-nav="next"
      title="Mes siguiente"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
    </button>
  </div>
</div>

{{-- Días de la semana --}}
<div class="grid grid-cols-7 gap-1 mb-2">
  @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $dayName)
    <div class="p-2 text-center text-xs font-medium text-slate-500">
      {{ $dayName }}
    </div>
  @endforeach
</div>

{{-- Grid del calendario --}}
<div class="grid grid-cols-7 gap-1" id="calendar-grid">
  @while($cursor->lte($endOfGrid))
    @php
      $inMonth   = $cursor->between($startOfMonth, $endOfMonth);
      $key       = $cursor->toDateString();
      $count     = $perDay[$key] ?? 0;
      $selected  = $key === $day->toDateString();
      $isToday   = $key === now()->toDateString();
      
      // Colores basados en la cantidad de citas
      $bgColor = match(true) {
        $count >= 10 => 'bg-rose-50 border-rose-200',
        $count >= 6  => 'bg-amber-50 border-amber-200',
        $count >= 1  => 'bg-emerald-50 border-emerald-200',
        default      => $inMonth ? 'bg-white border-slate-200' : 'bg-slate-50 border-slate-100',
      };
      
      $textColor = $inMonth ? 'text-slate-800' : 'text-slate-400';
      $borderStyle = $selected ? 'ring-2 ring-blue-500 border-blue-200' : 'border';
      $todayStyle = $isToday && !$selected ? 'bg-blue-100 text-blue-800' : '';
    @endphp
    
    <button
      class="p-2 h-24 rounded-lg border text-sm flex flex-col justify-between text-left transition-all duration-200 hover:shadow-md {{ $bgColor }} {{ $borderStyle }} {{ $textColor }}"
      data-day="{{ $key }}"
      data-month="{{ $month->format('Y-m') }}"
      title="{{ $count }} cita(s) para este día"
    >
      {{-- Número del día --}}
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium {{ $todayStyle }} {{ $selected ? 'text-blue-600' : '' }}">
          {{ $cursor->day }}
        </span>
        @if ($isToday && $inMonth)
          <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-blue-600 text-white font-medium">
            hoy
          </span>
        @endif
      </div>

      {{-- Indicador de citas --}}
      @if ($count > 0)
        <div class="flex justify-end">
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
            {{ $count >= 10 ? 'bg-rose-100 text-rose-800' : 
               ($count >= 6 ? 'bg-amber-100 text-amber-800' : 
               'bg-emerald-100 text-emerald-800') }}">
            {{ $count }}
          </span>
        </div>
      @else
        <div class="h-6"></div> {{-- Espacio reservado --}}
      @endif
    </button>
    
    @php $cursor->addDay(); @endphp
  @endwhile
</div>

{{-- Leyenda --}}
<div class="mt-4 pt-4 border-t border-slate-200">
  <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600">
    <span class="flex items-center gap-2">
      <span class="w-3 h-3 rounded bg-emerald-100 border border-emerald-200"></span>
      1-5 citas
    </span>
    <span class="flex items-center gap-2">
      <span class="w-3 h-3 rounded bg-amber-100 border border-amber-200"></span>
      6-9 citas
    </span>
    <span class="flex items-center gap-2">
      <span class="w-3 h-3 rounded bg-rose-100 border border-rose-200"></span>
      10+ citas
    </span>
  </div>
</div>