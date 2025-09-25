@extends('layouts.app')
@section('title','Historia completa')

@section('header-actions')
  <a href="{{ route('admin.patients.show',$patient) }}" class="btn btn-ghost">Volver</a>
@endsection

@section('content')
  <div class="card">
    <div class="mb-3">
      <h3 class="font-semibold">Historia completa</h3>
      <p class="text-sm text-slate-500">
        Paciente: <span class="font-medium">{{ $patient->last_name }}, {{ $patient->first_name }}</span>
      </p>
    </div>

    @if($events->isEmpty())
      <p class="text-sm text-slate-500">Sin eventos clínicos registrados.</p>
    @else
      <div class="relative">
        <div class="absolute left-3 top-0 bottom-0 border-s"></div>
        <ul class="space-y-4">
          @foreach($events as $e)
            @php
              $badge = match($e['type'] ?? '') {
                'appointment' => 'bg-blue-100 text-blue-700',
                'note'        => 'bg-amber-100 text-amber-700',
                'diagnosis'   => 'bg-rose-100 text-rose-700',
                'treatment'   => 'bg-emerald-100 text-emerald-700',
                'odontogram'  => 'bg-sky-100 text-sky-700',
                'consent'     => 'bg-purple-100 text-purple-700',
                'attachment'  => 'bg-slate-100 text-slate-700',
                'payment'     => 'bg-teal-100 text-teal-700',
                default       => 'bg-slate-100 text-slate-700',
              };

              $ts = $e['ts'] instanceof \Illuminate\Support\Carbon ? $e['ts'] : \Illuminate\Support\Carbon::parse($e['ts'] ?? now());
              $url = $e['url'] ?? '#';
            @endphp

            <li class="relative pl-10">
              <span class="absolute left-0 top-2 h-3 w-3 rounded-full bg-white border"></span>

              <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                  <div class="text-xs text-slate-500">{{ $ts->format('Y-m-d H:i') }}</div>
                  <div class="font-medium truncate">{{ $e['title'] ?? 'Evento' }}</div>

                  @if(!empty($e['meta']))
                    <div class="text-sm text-slate-600 break-words">{{ $e['meta'] }}</div>
                  @endif

                  @if(!empty($e['status']))
                    <span class="inline-block mt-1 text-[11px] px-2 py-0.5 rounded {{ $badge }}">
                      {{ str_replace('_',' ', ucfirst($e['status'])) }}
                    </span>
                  @endif
                </div>

                <div class="shrink-0">
                  <a href="{{ $url }}" class="btn btn-ghost">Ver</a>
                </div>
              </div>
            </li>
          @endforeach
        </ul>
      </div>
    @endif
  </div>
@endsection
