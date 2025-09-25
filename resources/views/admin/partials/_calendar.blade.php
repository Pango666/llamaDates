@php
  $startOfMonth = $month->copy()->startOfMonth();
  $endOfMonth   = $month->copy()->endOfMonth();
  $startOfGrid  = $startOfMonth->copy()->startOfWeek(); // Lunes
  $endOfGrid    = $endOfMonth->copy()->endOfWeek();     // Domingo
  $cursor       = $startOfGrid->copy();

  $btn = 'px-3 py-1 rounded bg-slate-100 hover:bg-slate-200 text-sm';
@endphp

<div class="flex items-center justify-between mb-3">
  <h3 id="month-label" class="font-semibold">{{ $month->translatedFormat('F Y') }}</h3>
  <div class="space-x-2">
    <button class="{{ $btn }}" data-nav="prev">«</button>
    <button class="{{ $btn }}" data-nav="today">Hoy</button>
    <button class="{{ $btn }}" data-nav="next">»</button>
  </div>
</div>

<div class="grid grid-cols-7 text-xs text-slate-500 mb-1">
  <div class="p-2">Mon</div><div class="p-2">Tue</div><div class="p-2">Wed</div>
  <div class="p-2">Thu</div><div class="p-2">Fri</div><div class="p-2">Sat</div><div class="p-2">Sun</div>
</div>

<div class="grid grid-cols-7 gap-1" id="calendar-grid">
  @while($cursor->lte($endOfGrid))
    @php
      $inMonth   = $cursor->between($startOfMonth, $endOfMonth);
      $key       = $cursor->toDateString();
      $count     = $perDay[$key] ?? 0;
      $selected  = $key === $day->toDateString();
      $bg = match(true) {
        $count >= 10 => 'bg-rose-100 border-rose-200',
        $count >= 6  => 'bg-amber-100 border-amber-200',
        $count >= 1  => 'bg-emerald-50 border-emerald-200',
        default      => $inMonth ? 'bg-white' : 'bg-slate-50',
      };
      $ring = $selected ? 'ring-2 ring-blue-500' : '';
    @endphp
    <button
      class="p-2 h-24 rounded border text-sm flex flex-col justify-between text-left {{ $bg }} {{ $ring }} hover:shadow-sm transition"
      data-day="{{ $key }}"
      data-month="{{ $month->format('Y-m') }}"
      title="{{ $count }} paciente(s)">
      <div class="flex items-center justify-between">
        <span class="{{ $inMonth ? '' : 'text-slate-400' }}">{{ $cursor->day }}</span>
        @if ($key === now()->toDateString())
          <span class="text-[10px] px-1 rounded bg-blue-100 text-blue-700">hoy</span>
        @endif
      </div>
      @if ($count > 0)
        <span class="self-start text-[11px] px-2 py-0.5 rounded bg-white/70 border border-white">
          {{ $count }} paciente{{ $count>1?'s':'' }}
        </span>
      @endif
    </button>
    @php $cursor->addDay(); @endphp
  @endwhile
</div>
