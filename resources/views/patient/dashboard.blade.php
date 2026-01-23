@extends('patient.layout')
@section('title','Mi panel')

@php
  use Carbon\Carbon;

  // Traducción + estilos de estados (ajusta si tienes otros)
  $statusMap = [
    'reserved'   => ['label' => 'Reservada',   'cls' => 'bg-amber-50 text-amber-700 border-amber-200'],
    'confirmed'  => ['label' => 'Confirmada',  'cls' => 'bg-blue-50 text-blue-700 border-blue-200'],
    'in_service' => ['label' => 'En atención', 'cls' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
    'completed'  => ['label' => 'Finalizada',  'cls' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
    'cancelled'  => ['label' => 'Cancelada',   'cls' => 'bg-rose-50 text-rose-700 border-rose-200'],
    'no_show'    => ['label' => 'No asistió',  'cls' => 'bg-slate-50 text-slate-700 border-slate-200'],
  ];

  $fmtDate = fn($d) => Carbon::parse($d)->translatedFormat('D, d M Y');
  $fmtTime = fn($t) => \Illuminate\Support\Str::substr((string)$t, 0, 5);
@endphp

@section('pt')
  {{-- Header interno --}}
  <div class="mb-5">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="text-xl font-bold text-slate-800" style="font-family:'Outfit',sans-serif;">Bienvenido</h1>
        <p class="text-sm text-slate-500">Aquí ves tus próximas citas, pagos recientes y accesos rápidos.</p>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="{{ route('app.appointments.create') }}" class="btn btn-primary">
          <i class="fas fa-plus"></i>
          Reservar cita
        </a>
        <a href="{{ route('app.appointments.index') }}" class="btn btn-ghost border border-slate-200">
          <i class="fas fa-calendar-check"></i>
          Ver mis citas
        </a>
        <a href="{{ route('app.invoices.index') }}" class="btn btn-ghost border border-slate-200">
          <i class="fas fa-credit-card"></i>
          Ver mis pagos
        </a>
      </div>
    </div>
  </div>

  {{-- KPIs simples --}}
  @php
    $upcomingCount = $nextAppointments?->count() ?? 0;
    $invoiceCount  = $lastInvoices?->count() ?? 0;
    $nextOne = $nextAppointments->first() ?? null;
  @endphp

  <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-6">
    {{-- Próximas citas --}}
    <div class="card group bg-gradient-to-br from-teal-50 to-cyan-50 border border-teal-100 hover:shadow-lg hover:scale-[1.02] transition-all duration-300">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs uppercase tracking-wide font-semibold text-teal-600">Próximas citas</div>
          <div class="text-3xl font-bold text-slate-800 mt-1">{{ $upcomingCount }}</div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-500 to-cyan-600 shadow-lg shadow-teal-500/25 flex items-center justify-center group-hover:shadow-teal-500/40 transition-shadow">
          <i class="fas fa-calendar-check text-white text-lg"></i>
        </div>
      </div>
      <div class="text-xs text-slate-600 mt-3 pt-3 border-t border-teal-100">
        @if($nextOne)
          <span class="inline-flex items-center gap-1">
            <i class="fas fa-clock text-teal-500"></i>
            Próxima: {{ $fmtDate($nextOne->date) }} · {{ $fmtTime($nextOne->start_time) }}
          </span>
        @else
          <span class="text-slate-500">No tienes citas programadas.</span>
        @endif
      </div>
    </div>

    {{-- Pagos recientes --}}
    <div class="card group bg-gradient-to-br from-emerald-50 to-green-50 border border-emerald-100 hover:shadow-lg hover:scale-[1.02] transition-all duration-300">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs uppercase tracking-wide font-semibold text-emerald-600">Pagos recientes</div>
          <div class="text-3xl font-bold text-slate-800 mt-1">{{ $invoiceCount }}</div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg shadow-emerald-500/25 flex items-center justify-center group-hover:shadow-emerald-500/40 transition-shadow">
          <i class="fas fa-credit-card text-white text-lg"></i>
        </div>
      </div>
      <div class="text-xs text-slate-600 mt-3 pt-3 border-t border-emerald-100">
        @if($lastInvoices->first())
          <span class="inline-flex items-center gap-1">
            <i class="fas fa-receipt text-emerald-500"></i>
            Último: {{ $lastInvoices->first()->created_at->format('Y-m-d') }}
          </span>
        @else
          <span class="text-slate-500">Aún no tienes recibos.</span>
        @endif
      </div>
    </div>

    {{-- Accesos rápidos --}}
    <div class="card group bg-gradient-to-br from-violet-50 to-purple-50 border border-violet-100 hover:shadow-lg transition-all duration-300">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs uppercase tracking-wide font-semibold text-violet-600">Accesos rápidos</div>
          <div class="text-base font-bold text-slate-800 mt-1">Perfil y datos</div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/25 flex items-center justify-center group-hover:shadow-violet-500/40 transition-shadow">
          <i class="fas fa-user text-white text-lg"></i>
        </div>
      </div>

      <div class="mt-3 pt-3 border-t border-violet-100 flex flex-wrap gap-2">
        <a href="{{ route('app.profile') }}" class="btn btn-ghost border border-violet-200 text-violet-700 hover:bg-violet-50">
          <i class="fas fa-id-card"></i> Mi perfil
        </a>
        <a href="{{ route('app.odontogram') }}" class="btn btn-ghost border border-violet-200 text-violet-700 hover:bg-violet-50">
          <i class="fas fa-teeth"></i> Odontograma
        </a>
      </div>
    </div>
  </div>

  {{-- Contenido principal --}}
  <div class="grid gap-4 lg:grid-cols-3">
    {{-- Próximas citas --}}
    <section class="card border border-slate-200 lg:col-span-2">
      <div class="flex items-center justify-between gap-3">
        <div>
          <h3 class="font-semibold text-slate-800">Próximas citas</h3>
          <p class="text-xs text-slate-500 mt-1">Revisa estado, horario y acciones disponibles.</p>
        </div>
        <a href="{{ route('app.appointments.index') }}" class="btn btn-ghost border border-slate-200">
          Ver todas
        </a>
      </div>

      <div class="mt-4 space-y-3">
        @forelse($nextAppointments as $a)
          @php
            $h = strlen($a->start_time) === 5 ? $a->start_time.':00' : $a->start_time;
            $when = Carbon::parse($a->date)->setTimeFromTimeString($h);
            $isPast = now()->gt($when);
            $rawStatus = $a->status ?? 'reserved';
            $st = $statusMap[$rawStatus] ?? ['label' => ucfirst(str_replace('_',' ',$rawStatus)), 'cls' => 'bg-slate-50 text-slate-700 border-slate-200'];
            $canCancel = now()->lt($when) && in_array($rawStatus, ['reserved','confirmed']);
          @endphp

          <div class="border border-slate-200 rounded-lg p-3 hover:bg-slate-50 transition">
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                  <span class="inline-flex items-center text-xs px-2 py-1 rounded border {{ $st['cls'] }}">
                    {{ $st['label'] }}
                  </span>
                  @if(!$isPast && $when->diffInDays(now()) <= 3)
                    <span class="inline-flex items-center text-xs px-2 py-1 rounded border bg-green-50 text-green-700 border-green-200">
                      Próxima
                    </span>
                  @endif
                </div>

                <div class="font-semibold text-slate-800 truncate">
                  {{ $a->service->name ?? 'Consulta' }}
                </div>

                <div class="text-xs text-slate-500 mt-1 flex flex-wrap gap-x-3 gap-y-1">
                  <span class="inline-flex items-center gap-1">
                    <i class="fas fa-calendar text-slate-400"></i>
                    {{ $fmtDate($a->date) }}
                  </span>
                  <span class="inline-flex items-center gap-1">
                    <i class="fas fa-clock text-slate-400"></i>
                    {{ $fmtTime($a->start_time) }}
                  </span>
                  <span class="inline-flex items-center gap-1">
                    <i class="fas fa-user-doctor text-slate-400"></i>
                    {{ $a->dentist->name ?? '—' }}
                  </span>
                </div>
              </div>

              <div class="shrink-0 flex items-center gap-2">
                <a href="{{ route('app.appointments.index') }}" class="btn btn-ghost border border-slate-200">
                  <i class="fas fa-eye"></i>
                  Ver
                </a>

                @if($canCancel)
                  <form method="post" action="{{ route('app.appointments.cancel',$a) }}" onsubmit="return confirm('¿Cancelar cita?');">
                    @csrf
                    <button class="btn btn-danger">
                      <i class="fas fa-xmark"></i>
                      Cancelar
                    </button>
                  </form>
                @endif
              </div>
            </div>
          </div>
        @empty
          <div class="text-sm text-slate-500 border border-dashed border-slate-300 rounded-lg p-6 text-center">
            No tienes próximas citas.
            <div class="mt-3">
              <a href="{{ route('app.appointments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Reservar cita
              </a>
            </div>
          </div>
        @endforelse
      </div>
    </section>

    {{-- Recibos recientes --}}
    <section class="card border border-slate-200">
      <div class="flex items-center justify-between gap-3">
        <div>
          <h3 class="font-semibold text-slate-800">Recibos recientes</h3>
          <p class="text-xs text-slate-500 mt-1">Accede a tus comprobantes.</p>
        </div>
        <a href="{{ route('app.invoices.index') }}" class="btn btn-ghost border border-slate-200">Ver todas</a>
      </div>

      <div class="mt-4 space-y-2">
        @forelse($lastInvoices as $inv)
          <a class="block border border-slate-200 rounded-lg p-3 hover:bg-slate-50 transition"
             href="{{ route('app.invoices.show',$inv) }}">
            <div class="flex items-center justify-between">
              <div class="font-semibold text-slate-800">
                #{{ $inv->number ?? ('FAC-'.$inv->id) }}
              </div>
              <div class="text-xs text-slate-500">
                {{ optional($inv->created_at)->format('Y-m-d') }}
              </div>
            </div>
            <div class="text-xs text-slate-500 mt-1 flex items-center gap-2">
              <span class="inline-flex items-center gap-1">
                <i class="fas fa-receipt text-slate-400"></i>
                Items: {{ $inv->items_count ?? ($inv->items?->count() ?? 0) }}
              </span>
            </div>
          </a>
        @empty
          <div class="text-sm text-slate-500 border border-dashed border-slate-300 rounded-lg p-6 text-center">
            Aún sin recibos.
          </div>
        @endforelse
      </div>
    </section>
  </div>
@endsection
