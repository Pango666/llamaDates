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

    $totalCitas = array_sum($statusCounts);
  @endphp

  {{-- Header Section --}}
  <div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-800" style="font-family: 'Outfit', sans-serif;">Gestión de Citas</h1>
        <p class="text-slate-500 text-sm mt-1">Administra y da seguimiento a todas las citas del consultorio</p>
      </div>
      <div class="flex items-center gap-2 text-sm">
        <span class="px-3 py-1.5 bg-slate-100 rounded-full text-slate-600 font-medium">
          {{ $totalCitas }} citas totales
        </span>
      </div>
    </div>
  </div>

  {{-- Quick Stats Cards --}}
  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    {{-- Reservadas --}}
    <a href="{{ $makeUrl(['status'=>'reserved']) }}" 
       class="group relative overflow-hidden rounded-xl p-4 bg-gradient-to-br from-amber-50 to-yellow-50 border border-amber-100 hover:shadow-lg hover:scale-[1.02] transition-all duration-300">
      <div class="absolute top-0 right-0 w-16 h-16 bg-amber-200/30 rounded-full -mr-6 -mt-6"></div>
      <div class="relative">
        <div class="flex items-center gap-2 mb-2">
          <div class="w-3 h-3 rounded-full bg-amber-400 animate-pulse"></div>
          <span class="text-xs font-semibold text-amber-700 uppercase tracking-wide">Reservadas</span>
        </div>
        <div class="text-3xl font-bold text-amber-600">{{ $statusCounts['reserved'] }}</div>
      </div>
    </a>

    {{-- Confirmadas --}}
    <a href="{{ $makeUrl(['status'=>'confirmed']) }}" 
       class="group relative overflow-hidden rounded-xl p-4 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 hover:shadow-lg hover:scale-[1.02] transition-all duration-300">
      <div class="absolute top-0 right-0 w-16 h-16 bg-blue-200/30 rounded-full -mr-6 -mt-6"></div>
      <div class="relative">
        <div class="flex items-center gap-2 mb-2">
          <div class="w-3 h-3 rounded-full bg-blue-500"></div>
          <span class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Confirmadas</span>
        </div>
        <div class="text-3xl font-bold text-blue-600">{{ $statusCounts['confirmed'] }}</div>
      </div>
    </a>

    {{-- En Atención --}}
    <a href="{{ $makeUrl(['status'=>'in_service']) }}" 
       class="group relative overflow-hidden rounded-xl p-4 bg-gradient-to-br from-orange-50 to-amber-50 border border-orange-100 hover:shadow-lg hover:scale-[1.02] transition-all duration-300">
      <div class="absolute top-0 right-0 w-16 h-16 bg-orange-200/30 rounded-full -mr-6 -mt-6"></div>
      <div class="relative">
        <div class="flex items-center gap-2 mb-2">
          <div class="w-3 h-3 rounded-full bg-orange-500 animate-pulse"></div>
          <span class="text-xs font-semibold text-orange-700 uppercase tracking-wide">En Atención</span>
        </div>
        <div class="text-3xl font-bold text-orange-600">{{ $statusCounts['in_service'] }}</div>
      </div>
    </a>

    {{-- Atendidas --}}
    <a href="{{ $makeUrl(['status'=>'done']) }}" 
       class="group relative overflow-hidden rounded-xl p-4 bg-gradient-to-br from-emerald-50 to-green-50 border border-emerald-100 hover:shadow-lg hover:scale-[1.02] transition-all duration-300">
      <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-200/30 rounded-full -mr-6 -mt-6"></div>
      <div class="relative">
        <div class="flex items-center gap-2 mb-2">
          <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
          <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Atendidas</span>
        </div>
        <div class="text-3xl font-bold text-emerald-600">{{ $statusCounts['done'] }}</div>
      </div>
    </a>

    {{-- No Asistieron --}}
    <a href="{{ $makeUrl(['status'=>'no_show']) }}" 
       class="group relative overflow-hidden rounded-xl p-4 bg-gradient-to-br from-rose-50 to-red-50 border border-rose-100 hover:shadow-lg hover:scale-[1.02] transition-all duration-300">
      <div class="absolute top-0 right-0 w-16 h-16 bg-rose-200/30 rounded-full -mr-6 -mt-6"></div>
      <div class="relative">
        <div class="flex items-center gap-2 mb-2">
          <div class="w-3 h-3 rounded-full bg-rose-500"></div>
          <span class="text-xs font-semibold text-rose-700 uppercase tracking-wide">No Asistió</span>
        </div>
        <div class="text-3xl font-bold text-rose-600">{{ $statusCounts['no_show'] }}</div>
      </div>
    </a>

    {{-- Canceladas --}}
    <a href="{{ $makeUrl(['status'=>'canceled']) }}" 
       class="group relative overflow-hidden rounded-xl p-4 bg-gradient-to-br from-slate-50 to-gray-100 border border-slate-200 hover:shadow-lg hover:scale-[1.02] transition-all duration-300">
      <div class="absolute top-0 right-0 w-16 h-16 bg-slate-200/50 rounded-full -mr-6 -mt-6"></div>
      <div class="relative">
        <div class="flex items-center gap-2 mb-2">
          <div class="w-3 h-3 rounded-full bg-slate-400"></div>
          <span class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Canceladas</span>
        </div>
        <div class="text-3xl font-bold text-slate-600">{{ $statusCounts['canceled'] }}</div>
      </div>
    </a>
  </div>

  {{-- Filters Card --}}
  <div class="card mb-6">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
          </svg>
        </div>
        <div>
          <h3 class="font-semibold text-slate-800">Filtros de Búsqueda</h3>
          <p class="text-xs text-slate-500">Refina los resultados por fecha, odontólogo o estado</p>
        </div>
      </div>
      @if(request()->hasAny(['date','dentist_id','status','q']))
        <a href="{{ route('admin.appointments.index') }}" 
           class="text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Limpiar filtros
        </a>
      @endif
    </div>

    <form method="get" class="grid gap-4 md:grid-cols-12 items-end">
      <div class="md:col-span-3">
        <label class="block text-xs font-semibold text-slate-600 mb-1.5">
          <span class="flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Fecha
          </span>
        </label>
        <input type="date" name="date" value="{{ $filters['date'] ?? '' }}"
               class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all bg-white">
      </div>

      <div class="md:col-span-3">
        <label class="block text-xs font-semibold text-slate-600 mb-1.5">
          <span class="flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Odontólogo
          </span>
        </label>
        <select name="dentist_id"
                class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all bg-white">
          <option value="">Todos los odontólogos</option>
          @foreach($dentists as $d)
            <option value="{{ $d->id }}" @selected(($filters['dentist_id'] ?? null)==$d->id)>{{ $d->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1.5">
          <span class="flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Estado
          </span>
        </label>
        <select name="status"
                class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all bg-white">
          <option value="">Todos los estados</option>
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
        <label class="block text-xs font-semibold text-slate-600 mb-1.5">
          <span class="flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Búsqueda
          </span>
        </label>
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
               placeholder="Paciente, servicio, teléfono..."
               class="w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all bg-white">
      </div>

      <div class="md:col-span-1">
        <button class="w-full btn btn-primary py-2.5 flex items-center justify-center gap-2">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="11" cy="11" r="7" stroke-width="2"/>
            <path d="M20 20l-3-3" stroke-width="2" stroke-linecap="round"/>
          </svg>
          Buscar
        </button>
      </div>
    </form>
  </div>

  {{-- Results Table --}}
  <div class="card p-0 overflow-hidden">
    <div class="px-5 py-4 border-b bg-gradient-to-r from-slate-50 to-white flex items-center justify-between">
      <div class="flex items-center gap-3">
        <h3 class="font-semibold text-slate-700">Listado de Citas</h3>
        <span class="px-2.5 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
          {{ $appointments->total() }} resultados
        </span>
      </div>
    </div>

    <div class="overflow-auto">
      <table class="min-w-full">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-100">
            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Horario</th>
            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Paciente</th>
            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Servicio</th>
            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Odontólogo</th>
            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
            <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-100">
          @forelse($appointments as $a)
            @php
              $statusKey = in_array($a->estatus, ['no_show','non-attendance'], true) ? 'no_show' : $a->estatus;

              $badgeStyles = [
                'reserved'   => 'bg-amber-50 text-amber-700 border-amber-200',
                'confirmed'  => 'bg-blue-50 text-blue-700 border-blue-200',
                'in_service' => 'bg-orange-50 text-orange-700 border-orange-200',
                'done'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'no_show'    => 'bg-rose-50 text-rose-700 border-rose-200',
                'canceled'   => 'bg-slate-100 text-slate-500 border-slate-200',
              ];

              $dotColors = [
                'reserved'   => 'bg-amber-400',
                'confirmed'  => 'bg-blue-500',
                'in_service' => 'bg-orange-500',
                'done'       => 'bg-emerald-500',
                'no_show'    => 'bg-rose-500',
                'canceled'   => 'bg-slate-400',
              ];

              $labels = [
                'reserved'=>'Reservado',
                'confirmed'=>'Confirmado',
                'in_service'=>'En atención',
                'done'=>'Atendido',
                'no_show'=>'No asistió',
                'canceled'=>'Cancelado',
              ];

              $badge = $badgeStyles[$statusKey] ?? 'bg-slate-100 text-slate-700 border-slate-200';
              $dot = $dotColors[$statusKey] ?? 'bg-slate-400';
              $label = $labels[$statusKey] ?? $a->estatus;

              $endTs = \Illuminate\Support\Carbon::parse($filters['date'] ?? now()->toDateString())->setTimeFromTimeString($a->end_time ?? '00:00:00');
              $locked = $endTs->isPast();
              $readOnlyStatus = in_array($statusKey, ['done','canceled','no_show'], true);
            @endphp

            <tr class="group hover:bg-blue-50/30 transition-colors">
              {{-- Horario --}}
              <td class="px-5 py-4 whitespace-nowrap">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </div>
                  <div>
                    <div class="font-semibold text-slate-800">
                      {{ \Illuminate\Support\Str::substr($a->start_time,0,5) }}
                    </div>
                    <div class="text-xs text-slate-500">
                      hasta {{ \Illuminate\Support\Str::substr($a->end_time,0,5) }}
                    </div>
                  </div>
                </div>
              </td>

              {{-- Paciente --}}
              <td class="px-5 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-600 font-semibold text-sm">
                    {{ strtoupper(substr($a->patient->first_name ?? '', 0, 1) . substr($a->patient->last_name ?? '', 0, 1)) }}
                  </div>
                  <div>
                    <div class="font-medium text-slate-800">{{ $a->patient->last_name }}, {{ $a->patient->first_name }}</div>
                    @if($a->patient->phone)
                      <div class="text-xs text-slate-500 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        {{ $a->patient->phone }}
                      </div>
                    @endif
                  </div>
                </div>
              </td>

              {{-- Servicio --}}
              <td class="px-5 py-4">
                <div class="font-medium text-slate-700">{{ $a->service->name }}</div>
              </td>

              {{-- Odontólogo --}}
              <td class="px-5 py-4">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </div>
                  <span class="text-slate-700">{{ $a->dentist->name }}</span>
                </div>
              </td>

              {{-- Estado --}}
              <td class="px-5 py-4">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border {{ $badge }}">
                  <span class="w-2 h-2 rounded-full {{ $dot }}"></span>
                  {{ $label }}
                </span>
              </td>

              {{-- Acciones --}}
              <td class="px-5 py-4">
                <div class="flex items-center justify-end gap-2">
                  @can('appointments.view')
                    <a href="{{ route('admin.appointments.show',$a) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-blue-600 hover:bg-blue-50 transition-colors">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                      Ver
                    </a>
                  @endcan

                  @if(!$locked && !$readOnlyStatus)
                    @can('appointments.update')
                      <form action="{{ route('admin.appointments.status',$a) }}" method="post" class="flex items-center">
                        @csrf
                        <select name="status"
                                class="border border-slate-200 rounded-lg px-2 py-1.5 text-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
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
                      @if($a->is_active && $a->estatus!=='canceled')
                        <form action="{{ route('admin.appointments.cancel',$a) }}" method="post"
                              onsubmit="return confirm('¿Cancelar esta cita?');">
                          @csrf
                          <button class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-rose-600 hover:bg-rose-50 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cancelar
                          </button>
                        </form>
                      @endif
                    @endcan
                  @else
                    <span class="text-xs text-slate-400 bg-slate-100 px-2.5 py-1.5 rounded-lg">Solo lectura</span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-5 py-16 text-center">
                <div class="inline-flex flex-col items-center">
                  <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                  </div>
                  <h4 class="font-semibold text-slate-700 mb-1">No se encontraron citas</h4>
                  <p class="text-sm text-slate-500 mb-4">Prueba ajustando los filtros de búsqueda</p>
                  @can('appointments.create')
                    <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      </svg>
                      Nueva Cita
                    </a>
                  @endcan
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Pagination --}}
  @if($appointments->hasPages())
    <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="text-sm text-slate-600">
        Mostrando <span class="font-semibold">{{ $appointments->firstItem() ?? 0 }}</span> - 
        <span class="font-semibold">{{ $appointments->lastItem() ?? 0 }}</span> de 
        <span class="font-semibold">{{ $appointments->total() }}</span> citas
      </div>
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
        {{ $appointments->links() }}
      </div>
    </div>
  @endif
@endsection
