@extends('layouts.app')
@section('title','Ocupación de sillas por día')

@section('header-actions')
  <a href="{{ route('admin.chairs.index') }}" class="btn btn-ghost">Volver a Sillas</a>
@endsection

@section('content')
  <div class="card">
    <div class="flex items-center justify-between mb-3">
      <h3 class="font-semibold">Ocupación semanal por silla</h3>
      <select id="daySel" class="border rounded px-2 py-1 text-sm">
        @foreach($dayLabels as $d=>$lbl)
          <option value="{{ $d }}">{{ $lbl }}</option>
        @endforeach
      </select>
    </div>

    @foreach($dayLabels as $d=>$lbl)
      <div class="day-panel" data-day="{{ $d }}" style="display:none">
        <h4 class="font-semibold mb-2">{{ $lbl }}</h4>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="border-b">
              <tr class="text-left">
                <th class="px-3 py-2">Silla</th>
                <th class="px-3 py-2">Bloques (Odontólogo · Hora)</th>
              </tr>
            </thead>
            <tbody>
              @forelse($chairs as $c)
                @php $rows = ($byDay[$d][$c->id] ?? collect()); @endphp
                <tr class="border-b align-top">
                  <td class="px-3 py-2 whitespace-nowrap font-medium">{{ $c->name }} <span class="text-xs text-slate-500">({{ $c->shift }})</span></td>
                  <td class="px-3 py-2">
                    @if($rows->isEmpty())
                      <span class="text-slate-500 text-xs">Libre</span>
                    @else
                      <ul class="space-y-1">
                        @foreach($rows as $r)
                          <li>• {{ $r['dentist'] }} · {{ $r['start'] }}–{{ $r['end'] }}</li>
                        @endforeach
                      </ul>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="2" class="px-3 py-6 text-center text-slate-500">Sin sillas registradas.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    @endforeach
  </div>

  <script>
    const sel = document.getElementById('daySel');
    const panels = document.querySelectorAll('.day-panel');
    function showDay(d){
      panels.forEach(p => p.style.display = (p.dataset.day === String(d)) ? '' : 'none');
    }
    sel.addEventListener('change', () => showDay(sel.value));
    // abrir en el día actual por defecto
    showDay(new Date().getDay());
    sel.value = new Date().getDay();
  </script>
@endsection
