@extends('patient.layout')
@section('title','Mi panel')

@section('content')
  <div class="grid gap-4 md:grid-cols-2">
    <section class="card">
      <h3 class="font-semibold mb-2">Próximas citas</h3>
      <div class="space-y-2">
        @forelse($nextAppointments as $a)
          <div class="border rounded px-3 py-2 flex items-center justify-between">
            <div>
              <div class="font-medium">{{ $a->service->name }}</div>
              <div class="text-xs text-slate-500">
                {{ \Illuminate\Support\Carbon::parse($a->date)->toDateString() }}
                · {{ \Illuminate\Support\Str::substr($a->start_time,0,5) }}
                con {{ $a->dentist->name }}
              </div>
            </div>
            @php
              $h = strlen($a->start_time) === 5 ? $a->start_time.':00' : $a->start_time;
              $when = \Carbon\Carbon::parse($a->date)->setTimeFromTimeString($h);
              $canCancel = now()->lt($when) && in_array($a->status,['reserved','confirmed']);
            @endphp
            @if($canCancel)
              <form method="post" action="{{ route('app.appointments.cancel',$a) }}" onsubmit="return confirm('¿Cancelar cita?');">
                @csrf
                <button class="btn btn-ghost">Cancelar</button>
              </form>
            @endif
          </div>
        @empty
          <div class="text-sm text-slate-500">No tienes próximas citas.</div>
        @endforelse
      </div>
      <div class="mt-3">
        <a href="{{ route('app.appointments.create') }}" class="btn btn-primary">Reservar cita</a>
      </div>
    </section>

    <section class="card">
      <h3 class="font-semibold mb-2">Recibos recientes</h3>
      <div class="space-y-2">
        @forelse($lastInvoices as $inv)
          <a class="block border rounded px-3 py-2 hover:bg-slate-50" href="{{ route('app.invoices.show',$inv) }}">
            <div class="flex items-center justify-between">
              <div class="font-medium">#{{ $inv->number }}</div>
              <div class="text-xs">{{ $inv->created_at->format('Y-m-d') }}</div>
            </div>
            <div class="text-xs text-slate-500">Items: {{ $inv->items_count }}</div>
          </a>
        @empty
          <div class="text-sm text-slate-500">Aún sin recibos.</div>
        @endforelse
      </div>
      <div class="mt-3"><a class="btn btn-ghost" href="{{ route('app.invoices.index') }}">Ver todas</a></div>
    </section>
  </div>
@endsection
