@php
  $teeth = collect($item['payload']['teeth'] ?? []);
  $notes = $item['payload']['notes'] ?? null;

  $sanos    = $teeth->where('status','sano')->pluck('code')->values();
  $ausentes = $teeth->where('status','ausente')->pluck('code')->values();

  // superficies agrupadas por condición
  $byCond = ['caries'=>[], 'obturado'=>[], 'sellado'=>[]];
  foreach ($teeth as $t) {
    foreach ($t['surfaces'] as $s) {
      $byCond[$s['condition']][] = $t['code'].'-'.$s['surface'];
    }
  }

  // helper para chips
  $chip = function($text, $cls) {
    return '<span class="inline-block text-xs rounded px-2 py-0.5 '.$cls.'">'.$text.'</span>';
  };
@endphp

<div class="space-y-2">
  @if($notes)
    <div class="text-sm text-slate-600 whitespace-pre-line">{{ $notes }}</div>
  @endif

  @if($sanos->count())
    <div class="text-sm">
      <span class="font-medium me-1">Piezas sanas:</span>
      {!! $chip($sanos->implode(', '), 'bg-emerald-50 text-emerald-700 border border-emerald-200') !!}
    </div>
  @endif

  @if($ausentes->count())
    <div class="text-sm">
      <span class="font-medium me-1">Ausentes:</span>
      {!! $chip($ausentes->implode(', '), 'bg-slate-100 text-slate-700 border') !!}
    </div>
  @endif

  @foreach (['caries'=>'Caries','obturado'=>'Obturado','sellado'=>'Sellado'] as $k=>$label)
    @if(!empty($byCond[$k]))
      <div class="text-sm">
        <span class="font-medium me-1">{{ $label }}:</span>
        @php
          $cls = match($k){
            'caries'   => 'bg-rose-50 text-rose-700 border border-rose-200',
            'obturado' => 'bg-amber-50 text-amber-800 border border-amber-200',
            'sellado'  => 'bg-sky-50 text-sky-700 border border-sky-200',
          };
        @endphp
        {!! $chip(collect($byCond[$k])->implode(', '), $cls) !!}
      </div>
    @endif
  @endforeach
</div>
