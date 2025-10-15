@extends('layouts.app')
@section('title','Gestión de Citas')

{{-- Botón en el header sticky --}}
@section('header-actions')
  <div class="flex gap-2">
    <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary flex items-center gap-2">
      <span>➕</span>
      Nueva cita
    </a>
  </div>
@endsection

@section('content')
  {{-- Resumen rápido --}}
  <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-4">
    @php
      $statusCounts = [
        'reserved' => $appointments->where('status', 'reserved')->count(),
        'confirmed' => $appointments->where('status', 'confirmed')->count(),
        'in_service' => $appointments->where('status', 'in_service')->count(),
        'done' => $appointments->where('status', 'done')->count(),
        'no_show' => $appointments->where('status', 'no_show')->count(),
        'canceled' => $appointments->where('status', 'canceled')->count(),
      ];
    @endphp
    
    <div class="card text-center p-3 bg-blue-50 border-blue-200">
      <div class="text-2xl font-bold text-blue-600">{{ $statusCounts['reserved'] }}</div>
      <div class="text-xs text-blue-700 font-medium">🟡 Reservadas</div>
    </div>
    
    <div class="card text-center p-3 bg-green-50 border-green-200">
      <div class="text-2xl font-bold text-green-600">{{ $statusCounts['confirmed'] }}</div>
      <div class="text-xs text-green-700 font-medium">🔵 Confirmadas</div>
    </div>
    
    <div class="card text-center p-3 bg-orange-50 border-orange-200">
      <div class="text-2xl font-bold text-orange-600">{{ $statusCounts['in_service'] }}</div>
      <div class="text-xs text-orange-700 font-medium">🟠 En atención</div>
    </div>
    
    <div class="card text-center p-3 bg-emerald-50 border-emerald-200">
      <div class="text-2xl font-bold text-emerald-600">{{ $statusCounts['done'] }}</div>
      <div class="text-xs text-emerald-700 font-medium">🟢 Atendidas</div>
    </div>
    
    <div class="card text-center p-3 bg-red-50 border-red-200">
      <div class="text-2xl font-bold text-red-600">{{ $statusCounts['no_show'] }}</div>
      <div class="text-xs text-red-700 font-medium">🔴 No asistieron</div>
    </div>
    
    <div class="card text-center p-3 bg-slate-100 border-slate-300">
      <div class="text-2xl font-bold text-slate-600">{{ $statusCounts['canceled'] }}</div>
      <div class="text-xs text-slate-700 font-medium">⚫ Canceladas</div>
    </div>
  </div>

  {{-- Filtros mejorados --}}
  <div class="card mb-4">
    <div class="flex items-center justify-between mb-3">
      <h3 class="font-semibold text-slate-700">🔍 Filtros de búsqueda</h3>
      @if(request()->hasAny(['date','dentist_id','status']))
        <a href="{{ route('admin.appointments.index') }}" class="btn btn-ghost text-sm flex items-center gap-1">
          <span>🔄</span>
          Limpiar filtros
        </a>
      @endif
    </div>
    
    <form method="get" class="grid gap-4 md:grid-cols-4">
      <div>
        <label class="block text-sm font-medium text-slate-600 mb-2 flex items-center gap-1">
          <span>📅</span>
          Fecha
        </label>
        <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" 
               class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
      </div>
      
      <div>
        <label class="block text-sm font-medium text-slate-600 mb-2 flex items-center gap-1">
          <span>👨‍⚕️</span>
          Odontólogo
        </label>
        <select name="dentist_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
          <option value="">— Todos los odontólogos —</option>
          @foreach($dentists as $d)
            <option value="{{ $d->id }}" @selected(($filters['dentist_id'] ?? null)==$d->id)>{{ $d->name }}</option>
          @endforeach
        </select>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-slate-600 mb-2 flex items-center gap-1">
          <span>📊</span>
          Estado
        </label>
        <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
          <option value="">— Todos los estados —</option>
          @foreach([
            'reserved' => '🟡 Reservado',
            'confirmed' => '🔵 Confirmado', 
            'in_service' => '🟠 En atención',
            'done' => '🟢 Atendido',
            'no_show' => '🔴 No asistió',
            'canceled' => '⚫ Cancelado'
          ] as $val=>$label)
            <option value="{{ $val }}" @selected(($filters['status'] ?? null)===$val)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      
      <div class="flex items-end">
        <button class="btn bg-blue-500 text-white hover:bg-blue-600 w-full flex items-center justify-center gap-2">
          <span>🔍</span>
          Buscar
        </button>
      </div>
    </form>
  </div>

  {{-- Tabla mejorada --}}
  <div class="card p-0 overflow-hidden">
    <div class="p-4 border-b bg-slate-50">
      <h3 class="font-semibold text-slate-700 flex items-center gap-2">
        <span>📋</span>
        Lista de Citas ({{ $appointments->total() }})
      </h3>
    </div>
    
    <div class="overflow-auto max-h-[65vh]">
      <table class="min-w-full text-sm">
        <thead class="sticky top-0 z-10 bg-white border-b">
          <tr class="text-left">
            <th class="px-4 py-3 bg-white font-semibold text-slate-600">Fecha</th>
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
              $dateStr = \Illuminate\Support\Carbon::parse($a->date)->toDateString();
              $endTs = \Illuminate\Support\Carbon::parse($dateStr)->setTimeFromTimeString($a->end_time);
              $locked = $endTs->isPast();
              $isToday = \Illuminate\Support\Carbon::parse($a->date)->isToday();

              $badge = [
                'reserved'   => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                'confirmed'  => 'bg-blue-100 text-blue-800 border border-blue-200',
                'in_service' => 'bg-orange-100 text-orange-800 border border-orange-200',
                'done'       => 'bg-green-100 text-green-800 border border-green-200',
                'no_show'    => 'bg-red-100 text-red-800 border border-red-200',
                'canceled'   => 'bg-slate-100 text-slate-500 border border-slate-300 line-through',
              ][$a->status] ?? 'bg-slate-100 text-slate-700';

              $statusIcons = [
                'reserved' => '🟡',
                'confirmed' => '🔵',
                'in_service' => '🟠',
                'done' => '🟢',
                'no_show' => '🔴',
                'canceled' => '⚫'
              ];
            @endphp
            
            <tr class="border-b hover:bg-slate-50 transition-colors {{ $isToday ? 'bg-blue-50 hover:bg-blue-100' : '' }}">
              <td class="px-4 py-3 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  @if($isToday)
                    <span class="text-blue-500 text-xs font-medium bg-blue-100 px-2 py-1 rounded-full">HOY</span>
                  @endif
                  <span class="{{ $isToday ? 'font-semibold text-blue-700' : 'text-slate-700' }}">
                    {{ \Illuminate\Support\Carbon::parse($a->date)->format('d/m/Y') }}
                  </span>
                </div>
              </td>
              
              <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-700">
                {{ \Illuminate\Support\Str::substr($a->start_time,0,5) }}–{{ \Illuminate\Support\Str::substr($a->end_time,0,5) }}
              </td>
              
              <td class="px-4 py-3">
                <div class="font-medium text-slate-800">{{ $a->patient->last_name }}, {{ $a->patient->first_name }}</div>
                @if($a->patient->phone)
                  <div class="text-xs text-slate-500 mt-1">{{ $a->patient->phone }}</div>
                @endif
              </td>
              
              <td class="px-4 py-3">
                <span class="text-slate-700">{{ $a->service->name }}</span>
              </td>
              
              <td class="px-4 py-3">
                <div class="text-slate-700">{{ $a->dentist->name }}</div>
              </td>
              
              <td class="px-4 py-3">
                <span class="badge {{ $badge }} text-xs font-medium flex items-center gap-1 w-fit">
                  <span>{{ $statusIcons[$a->status] ?? '⚪' }}</span>
                  {{ [
                    'reserved'=>'Reservado',
                    'confirmed'=>'Confirmado',
                    'in_service'=>'En atención',
                    'done'=>'Atendido',
                    'no_show'=>'No asistió',
                    'canceled'=>'Cancelado'
                  ][$a->status] ?? $a->status }}
                </span>
              </td>
              
              <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-2">
                  <a href="{{ route('admin.appointments.show',$a) }}" 
                     class="btn btn-ghost text-sm flex items-center gap-1 hover:bg-blue-50 hover:text-blue-600">
                    <span>👁️</span>
                    Ver
                  </a>

                  @if(!$locked && $a->status !== 'done' && $a->status !== 'canceled')
                    <div class="flex items-center gap-1">
                      {{-- Selector rápido de estado --}}
                      <form action="{{ route('admin.appointments.status',$a) }}" method="post" class="flex items-center">
                        @csrf
                        <select name="status" 
                                class="border border-slate-300 rounded px-2 py-1 text-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                onchange="this.form.submit()"
                                title="Cambiar estado">
                          @foreach([
                            'reserved' => '🟡 Reservado',
                            'confirmed' => '🔵 Confirmado', 
                            'in_service' => '🟠 En atención',
                            'done' => '🟢 Atendido',
                            'no_show' => '🔴 No asistió'
                          ] as $val=>$label)
                            <option value="{{ $val }}" @selected($a->status===$val)>{{ $label }}</option>
                          @endforeach
                        </select>
                      </form>

                      {{-- Botón cancelar --}}
                      @if($a->is_active && $a->status!=='canceled')
                        <form action="{{ route('admin.appointments.cancel',$a) }}" method="post" 
                              onsubmit="return confirm('¿Estás seguro de cancelar esta cita?');">
                          @csrf
                          <button class="btn btn-danger btn-sm flex items-center gap-1" title="Cancelar cita">
                            <span>❌</span>
                            Cancelar
                          </button>
                        </form>
                      @endif
                    </div>
                  @else
                    <span class="text-xs text-slate-400 bg-slate-100 px-2 py-1 rounded">Solo lectura</span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                <div class="flex flex-col items-center gap-2">
                  <span class="text-4xl">📭</span>
                  <div>
                    <div class="font-medium text-slate-600">No se encontraron citas</div>
                    <div class="text-sm text-slate-500 mt-1">
                      @if(request()->hasAny(['date','dentist_id','status']))
                        Intenta con otros filtros de búsqueda
                      @else
                        No hay citas programadas en este momento
                      @endif
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Paginación --}}
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

  {{-- Leyenda de estados --}}
  <div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200">
    <div class="text-xs font-medium text-slate-600 mb-2">📖 Leyenda de estados:</div>
    <div class="flex flex-wrap gap-3 text-xs">
      <div class="flex items-center gap-1">
        <span class="w-3 h-3 bg-yellow-100 border border-yellow-300 rounded-full"></span>
        <span>Reservado</span>
      </div>
      <div class="flex items-center gap-1">
        <span class="w-3 h-3 bg-blue-100 border border-blue-300 rounded-full"></span>
        <span>Confirmado</span>
      </div>
      <div class="flex items-center gap-1">
        <span class="w-3 h-3 bg-orange-100 border border-orange-300 rounded-full"></span>
        <span>En atención</span>
      </div>
      <div class="flex items-center gap-1">
        <span class="w-3 h-3 bg-green-100 border border-green-300 rounded-full"></span>
        <span>Atendido</span>
      </div>
      <div class="flex items-center gap-1">
        <span class="w-3 h-3 bg-red-100 border border-red-300 rounded-full"></span>
        <span>No asistió</span>
      </div>
      <div class="flex items-center gap-1">
        <span class="w-3 h-3 bg-slate-100 border border-slate-300 rounded-full"></span>
        <span>Cancelado</span>
      </div>
    </div>
  </div>
@endsection