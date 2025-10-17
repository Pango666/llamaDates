<div class="border-b border-slate-200 pb-4 mb-4">
  <div class="flex items-center justify-between">
    <h3 class="font-semibold text-slate-800 flex items-center gap-2">
      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      Citas del Día
    </h3>
    <div class="flex items-center gap-3">
      <span class="text-sm font-medium text-slate-600 bg-slate-100 px-3 py-1 rounded-full">
        {{ $day->translatedFormat('l, d F Y') }}
      </span>
      <a href="{{ route('admin.appointments.index') }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
        Ver todas
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
      </a>
    </div>
  </div>
</div>

@forelse ($appointments as $i => $appointment)
  @php
    $statusConfig = [
      'reserved'   => ['class' => 'bg-slate-100 text-slate-700', 'icon' => 'clock'],
      'confirmed'  => ['class' => 'bg-blue-100 text-blue-700', 'icon' => 'check'],
      'in_service' => ['class' => 'bg-amber-100 text-amber-700', 'icon' => 'play'],
      'done'       => ['class' => 'bg-emerald-100 text-emerald-700', 'icon' => 'check-circle'],
      'no_show'    => ['class' => 'bg-rose-100 text-rose-700', 'icon' => 'x'],
      'canceled'   => ['class' => 'bg-slate-200 text-slate-700 line-through', 'icon' => 'ban'],
    ];
    $statusInfo = $statusConfig[$appointment->status] ?? $statusConfig['reserved'];
  @endphp
  
  <div class="border border-slate-200 rounded-lg p-4 mb-3 bg-white hover:bg-slate-50 transition-colors duration-200">
    <div class="flex items-start justify-between mb-2">
      {{-- Información del paciente --}}
      <div class="flex-1">
        <div class="flex items-center gap-3 mb-2">
          <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <div>
            <h4 class="font-semibold text-slate-800">
              {{ $appointment->patient?->first_name }} {{ $appointment->patient?->last_name }}
            </h4>
            <p class="text-sm text-slate-600">{{ $appointment->service?->name }}</p>
          </div>
        </div>

        {{-- Horario --}}
        <div class="flex items-center gap-4 text-sm text-slate-600">
          <span class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ \Illuminate\Support\Str::of($appointment->start_time)->beforeLast(':') }} - {{ \Illuminate\Support\Str::of($appointment->end_time)->beforeLast(':') }}
          </span>
          @if($appointment->dentist)
            <span class="flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Dr. {{ $appointment->dentist->name }}
            </span>
          @endif
        </div>
      </div>

      {{-- Estado --}}
      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusInfo['class'] }}">
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          @if($statusInfo['icon'] === 'check')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          @elseif($statusInfo['icon'] === 'clock')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          @elseif($statusInfo['icon'] === 'play')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
          @elseif($statusInfo['icon'] === 'check-circle')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          @elseif($statusInfo['icon'] === 'x')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          @else
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
          @endif
        </svg>
        {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
      </span>
    </div>

    {{-- Acciones --}}
    <div class="flex items-center gap-2 pt-3 mt-3 border-t border-slate-200">
      <a 
        href="{{ route('admin.patients.show', $appointment->patient) }}" 
        class="btn btn-ghost flex items-center gap-1 text-xs"
        title="Ver perfil del paciente"
      >
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        Perfil
      </a>
      
      @if(in_array($appointment->status, ['reserved', 'confirmed', 'in_service']))
        <a 
          {{-- href="{{ route('admin.appointments.edit', $appointment) }}"  --}}
          class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-1 text-xs"
          title="Atender cita"
        >
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
          </svg>
          Atender
        </a>
      @endif
    </div>
  </div>
@empty
  <div class="text-center py-8">
    <svg class="w-12 h-12 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    <p class="text-slate-500 font-medium">No hay citas programadas</p>
    <p class="text-sm text-slate-400 mt-1">No hay citas agendadas para este día.</p>
    <a href="{{ route('admin.appointments.create') }}" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 mt-4 inline-flex">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      Agendar Cita
    </a>
  </div>
@endforelse