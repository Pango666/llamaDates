@extends('layouts.app')
@section('title', 'Ocupación de Consultorios por Día')

@section('header-actions')
  <a href="{{ route('admin.chairs.index') }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Consultorios
  </a>
@endsection

@section('content')
  <div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
          Ocupación Semanal de Consultorios
        </h1>
        <p class="text-sm text-slate-600 mt-1">Visualice la ocupación de los consultorios por día de la semana.</p>
      </div>
    </div>

    <div class="card">
      {{-- Selector de día --}}
      <div class="flex items-center justify-between mb-6">
        <h3 class="font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          Ocupación por Día
        </h3>
        <select 
          id="daySel" 
          class="border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
        >
          @foreach($dayLabels as $day => $label)
            <option value="{{ $day }}">{{ $label }}</option>
          @endforeach
        </select>
      </div>

      {{-- Paneles por día --}}
      @foreach($dayLabels as $day => $label)
        <div class="day-panel" data-day="{{ $day }}" style="display: none">
          <div class="border-b border-slate-200 pb-4 mb-4">
            <h4 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              {{ $label }}
            </h4>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                  <th class="px-4 py-3 font-semibold text-slate-700">Consultorio</th>
                  <th class="px-4 py-3 font-semibold text-slate-700">Bloques Programados</th>
                </tr>
              </thead>
              <tbody>
                @forelse($chairs as $chair)
                  @php $rows = ($byDay[$day][$chair->id] ?? collect()); @endphp
                  <tr class="border-b hover:bg-slate-50 transition-colors align-top">
                    <td class="px-4 py-3 whitespace-nowrap">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                          </svg>
                        </div>
                        <div>
                          <p class="font-medium text-slate-800">{{ $chair->name }}</p>
                          <p class="text-xs text-slate-500">{{ ucfirst($chair->shift) }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="px-4 py-3">
                      @if($rows->isEmpty())
                        <div class="flex items-center gap-2 text-slate-500">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                          </svg>
                          <span class="text-sm">Libre</span>
                        </div>
                      @else
                        <div class="space-y-2">
                          @foreach($rows as $row)
                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200">
                              <div class="flex-1">
                                <p class="font-medium text-slate-800">{{ $row['dentist'] }}</p>
                                <p class="text-xs text-slate-600">{{ $row['start'] }} – {{ $row['end'] }}</p>
                              </div>
                              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Ocupado
                              </span>
                            </div>
                          @endforeach
                        </div>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="2" class="px-4 py-8 text-center text-slate-500">
                      No hay consultorios registrados en el sistema.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  <script>
    const sel = document.getElementById('daySel');
    const panels = document.querySelectorAll('.day-panel');
    
    function showDay(day) {
      panels.forEach(p => p.style.display = (p.dataset.day === String(day)) ? 'block' : 'none');
    }
    
    sel.addEventListener('change', () => showDay(sel.value));
    
    // Mostrar día actual por defecto
    const today = new Date().getDay();
    showDay(today);
    sel.value = today;
  </script>
@endsection