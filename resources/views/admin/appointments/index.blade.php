@extends('layouts.app')
@section('title','Gestión de Citas')

@section('header-actions')
  <div class="flex gap-2">
    @can('appointments.create')
      <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary flex items-center gap-2">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M12 4v16M20 12H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Nueva cita
      </a>
    @endcan
  </div>
@endsection

@section('content')
  @php
    $filters = $filters ?? [];
    $statusCounts = $statusCounts ?? ['reserved'=>0,'confirmed'=>0,'in_service'=>0,'done'=>0,'no_show'=>0,'canceled'=>0];

    $makeUrl = function(array $extra = []) use ($filters) {
      $params = array_filter($filters, fn($v) => $v !== null && $v !== '');
      $params = array_merge($params, $extra);
      $params = array_filter($params, fn($v) => $v !== null && $v !== '');
      return route('admin.appointments.index', $params);
    };
  @endphp

  
  <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-4">
    <a href="{{ $makeUrl(['status'=>'reserved']) }}" class="card text-center p-3 bg-blue-50 border-blue-200 hover:brightness-[0.98] transition">
      <div class="text-2xl font-bold text-blue-600">{{ $statusCounts['reserved'] }}</div>
      <div class="text-xs text-blue-700 font-medium inline-flex items-center gap-1 justify-center">
        <span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span> Reservadas
      </div>
    </a>

    <a href="{{ $makeUrl(['status'=>'confirmed']) }}" class="card text-center p-3 bg-green-50 border-green-200 hover:brightness-[0.98] transition">
      <div class="text-2xl font-bold text-green-600">{{ $statusCounts['confirmed'] }}</div>
      <div class="text-xs text-green-700 font-medium inline-flex items-center gap-1 justify-center">
        <span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> Confirmadas
      </div>
    </a>

    <a href="{{ $makeUrl(['status'=>'in_service']) }}" class="card text-center p-3 bg-orange-50 border-orange-200 hover:brightness-[0.98] transition">
      <div class="text-2xl font-bold text-orange-600">{{ $statusCounts['in_service'] }}</div>
      <div class="text-xs text-orange-700 font-medium inline-flex items-center gap-1 justify-center">
        <span class="w-3 h-3 rounded-full bg-orange-500 inline-block"></span> En atención
      </div>
    </a>

    <a href="{{ $makeUrl(['status'=>'done']) }}" class="card text-center p-3 bg-emerald-50 border-emerald-200 hover:brightness-[0.98] transition">
      <div class="text-2xl font-bold text-emerald-600">{{ $statusCounts['done'] }}</div>
      <div class="text-xs text-emerald-700 font-medium inline-flex items-center gap-1 justify-center">
        <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span> Atendidas
      </div>
    </a>

    
    <a href="{{ $makeUrl(['status'=>'no_show']) }}" class="card text-center p-3 bg-red-50 border-red-200 hover:brightness-[0.98] transition">
      <div class="text-2xl font-bold text-red-600">{{ $statusCounts['no_show'] }}</div>
      <div class="text-xs text-red-700 font-medium inline-flex items-center gap-1 justify-center">
        <span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> No asistieron
      </div>
    </a>

    <a href="{{ $makeUrl(['status'=>'canceled']) }}" class="card text-center p-3 bg-slate-100 border-slate-300 hover:brightness-[0.98] transition">
      <div class="text-2xl font-bold text-slate-600">{{ $statusCounts['canceled'] }}</div>
      <div class="text-xs text-slate-700 font-medium inline-flex items-center gap-1 justify-center">
        <span class="w-3 h-3 rounded-full bg-slate-600 inline-block"></span> Canceladas
      </div>
    </a>
  </div>

  
{{-- Filtros alineados: 1 fila en desktop (Fecha / Odontólogo / Estado / Buscar / Acciones) --}}
<div class="card mb-4">
  <div class="mb-3">
    <h3 class="font-semibold text-slate-800">Buscar y filtrar</h3>
    <p class="text-xs text-slate-500">Refina la lista por fecha, odontólogo, estado o búsqueda.</p>
  </div>

  <form method="get" class="grid gap-3 md:grid-cols-12 items-end">
    <div class="md:col-span-3">
      <label class="block text-xs font-semibold text-slate-600 mb-1">Fecha</label>
      <input type="date" name="date" value="{{ $filters['date'] ?? '' }}"
             class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
    </div>

    <div class="md:col-span-3">
      <label class="block text-xs font-semibold text-slate-600 mb-1">Odontólogo</label>
      <select name="dentist_id"
              class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        <option value="">— Todos —</option>
        @foreach($dentists as $d)
          <option value="{{ $d->id }}" @selected(($filters['dentist_id'] ?? null)==$d->id)>{{ $d->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="md:col-span-2">
      <label class="block text-xs font-semibold text-slate-600 mb-1">Estado</label>
      <select name="status"
              class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        <option value="">— Todos —</option>
        @foreach([
          'reserved'   => 'Reservado',
          'confirmed'  => 'Confirmado',
          'in_service' => 'En atención',
          'done'       => 'Atendido',
          'no_show'    => 'No asistió',
          'canceled'   => 'Cancelado',
        ] as $val=>$label)
          <option value="{{ $val }}" @selected(($filters['status'] ?? null)===$val)>{{ $label }}</option>
        @endforeach
      </select>
    </div>

    <div class="md:col-span-3">
      <label class="block text-xs font-semibold text-slate-600 mb-1">Buscar</label>
      <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
             placeholder="Paciente / servicio / odontólogo / teléfono"
             class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
      <p class="text-[11px] text-slate-500 mt-1">Ej: “Pérez”, “Limpieza”, “Juan”, “7654321”.</p>
    </div>

    <div class="md:col-span-1">
      <div class="flex md:flex-col gap-2 md:items-stretch md:justify-end">
        @if(request()->hasAny(['date','dentist_id','status','q']))
          <a href="{{ route('admin.appointments.index') }}"
             class="btn btn-ghost border border-slate-200 hover:bg-slate-50 inline-flex items-center justify-center gap-2 w-full">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M4 4v6h6M20 20v-6h-6M20 4l-6 6M4 20l6-6"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Limpiar
          </a>
        @endif

        <button class="btn bg-blue-600 text-white hover:bg-blue-700 inline-flex items-center justify-center gap-2 w-full">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="11" cy="11" r="7" stroke-width="2"/>
            <path d="M20 20l-3-3" stroke-width="2" stroke-linecap="round"/>
          </svg>
          Aplicar
        </button>
      </div>
    </div>
  </form>
</div>



  
  <div class="card p-0 overflow-hidden">
    <div class="p-4 border-b bg-slate-50 flex items-center justify-between">
      <h3 class="font-semibold text-slate-700">Lista de citas ({{ $appointments->total() }})</h3>
    </div>

    <div class="overflow-auto max-h-[65vh]">
      <table class="min-w-full text-sm">
        <thead class="sticky top-0 z-10 bg-white border-b">
          <tr class="text-left">
            <th class="px-4 py-3 bg-white font-semibold text-slate-600">Hora</th>
            <th class="px-4 py-3 bg-white font-semibold text-slate-600">Paciente</th>
            <th class="px-4 py-3 bg-white font-semibold text-slate-600">Servicio</th>
            <th class="px-4 py-3 bg-white font-semibold text-slate-600">Odontólogo</th>
            <th class="px-4 py-3 bg-white font-semibold text-slate-600">Estado</th>
            <th class="px-4 py-3 bg-white font-semibold text-slate-600 text-right">Acciones</th>
          </tr>
        </thead>

        <tbody>
          @forelse($appointments as $a)
            @php
              // Unificar etiqueta y badge de inasistencia
              $statusKey = in_array($a->status, ['no_show','non-attendance'], true) ? 'no_show' : $a->status;

              $badge = [
                'reserved'   => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                'confirmed'  => 'bg-blue-100 text-blue-800 border border-blue-200',
                'in_service' => 'bg-orange-100 text-orange-800 border border-orange-200',
                'done'       => 'bg-green-100 text-green-800 border border-green-200',
                'no_show'    => 'bg-red-100 text-red-800 border border-red-200',
                'canceled'   => 'bg-slate-100 text-slate-500 border border-slate-300 line-through',
              ][$statusKey] ?? 'bg-slate-100 text-slate-700 border border-slate-200';

              $label = [
                'reserved'=>'Reservado',
                'confirmed'=>'Confirmado',
                'in_service'=>'En atención',
                'done'=>'Atendido',
                'no_show'=>'No asistió',
                'canceled'=>'Cancelado',
              ][$statusKey] ?? $a->status;

              $endTs = \Illuminate\Support\Carbon::parse($filters['date'])->setTimeFromTimeString($a->end_time);
              $locked = $endTs->isPast();

              $readOnlyStatus = in_array($statusKey, ['done','canceled','no_show'], true);
            @endphp

            <tr class="border-b hover:bg-slate-50 transition-colors">
              <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-700">
                {{ \Illuminate\Support\Str::substr($a->start_time,0,5) }}–{{ \Illuminate\Support\Str::substr($a->end_time,0,5) }}
              </td>

              <td class="px-4 py-3">
                <div class="font-medium text-slate-800">{{ $a->patient->last_name }}, {{ $a->patient->first_name }}</div>
                @if($a->patient->phone)
                  <div class="text-xs text-slate-500 mt-1">{{ $a->patient->phone }}</div>
                @endif
              </td>

              <td class="px-4 py-3 text-slate-700">{{ $a->service->name }}</td>
              <td class="px-4 py-3 text-slate-700">{{ $a->dentist->name }}</td>

              <td class="px-4 py-3">
                <span class="badge {{ $badge }} text-xs font-medium inline-flex items-center gap-1 w-fit">
                  <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"/></svg>
                  {{ $label }}
                </span>
              </td>

              <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-2">
                  @can('appointments.view')
                    <a href="{{ route('admin.appointments.show',$a) }}"
                       class="btn btn-ghost text-sm inline-flex items-center gap-1 text-blue-700 hover:bg-blue-50">
                      Ver
                    </a>
                  @endcan

                  @if(!$locked && !$readOnlyStatus)
                    <div class="flex items-center gap-1">
                      @can('appointments.update')
                        <form action="{{ route('admin.appointments.status',$a) }}" method="post" class="flex items-center">
                          @csrf
                          <select name="status"
                                  class="border border-slate-300 rounded px-2 py-1 text-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                  onchange="this.form.submit()">
                            @foreach([
                              'reserved' => 'Reservado',
                              'confirmed' => 'Confirmado',
                              'in_service' => 'En atención',
                              'done' => 'Atendido',
                              'no_show' => 'No asistió',
                            ] as $val=>$labelOpt)
                              <option value="{{ $val }}" @selected($statusKey===$val)>{{ $labelOpt }}</option>
                            @endforeach
                          </select>
                        </form>
                      @endcan

                      @can('appointments.cancel')
                        @if($a->is_active && $a->status!=='canceled')
                          <form action="{{ route('admin.appointments.cancel',$a) }}" method="post"
                                onsubmit="return confirm('¿Cancelar esta cita?');">
                            @csrf
                            <button class="btn btn-danger btn-sm">Cancelar</button>
                          </form>
                        @endif
                      @endcan
                    </div>
                  @else
                    <span class="text-xs text-slate-400 bg-slate-100 px-2 py-1 rounded">Solo lectura</span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                <div class="font-medium text-slate-600">No se encontraron citas</div>
                <div class="text-sm text-slate-500 mt-1">Prueba ajustando odontólogo, estado o búsqueda.</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  @if($appointments->hasPages())
    <div class="mt-4 flex items-center justify-between">
      <div class="text-sm text-slate-600">
        Mostrando {{ $appointments->firstItem() ?? 0 }} - {{ $appointments->lastItem() ?? 0 }} de {{ $appointments->total() }} citas
      </div>
      <div class="bg-white rounded-lg border border-slate-200">
        {{ $appointments->links() }}
      </div>
    </div>
  @endif
@endsection
