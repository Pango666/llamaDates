<div class="flex items-center justify-between mb-3">
  <h3 class="font-semibold">{{ $day->toDateString() }}</h3>
  <a href="{{ route('admin.appointments.index') }}" class="text-sm text-blue-600 hover:underline">Ver todas</a>
</div>

@forelse ($appointments as $i => $a)
  @php
    $badge = [
      'reserved'   => 'bg-slate-100 text-slate-700',
      'confirmed'  => 'bg-blue-100 text-blue-700',
      'in_service' => 'bg-amber-100 text-amber-700',
      'done'       => 'bg-emerald-100 text-emerald-700',
      'no_show'    => 'bg-rose-100 text-rose-700',
      'canceled'   => 'bg-slate-200 text-slate-700 line-through',
    ][$a->status] ?? 'bg-slate-100';
  @endphp
  <div class="flex items-center justify-between border rounded-lg p-3 mb-2 bg-white hover:bg-slate-50">
    <div>
      <div class="text-sm font-medium">
        {{ $i+1 }}. {{ $a->patient?->first_name }} {{ $a->patient?->last_name }}
      </div>
      <div class="text-xs text-slate-500">
        {{ \Illuminate\Support\Str::of($a->start_time)->beforeLast(':') }}–{{ \Illuminate\Support\Str::of($a->end_time)->beforeLast(':') }}
        · {{ $a->service?->name }}
      </div>
    </div>
    <div class="flex items-center gap-2">
      <span class="text-xs px-2 py-1 rounded {{ $badge }}">{{ ucfirst(str_replace('_',' ',$a->status)) }}</span>
      {{-- <a class="text-xs px-2 py-1 rounded bg-slate-100" href="{{ route('admin.patients') }}">Perfil</a> --}}
      <a class="text-xs px-2 py-1 rounded bg-slate-100" href="#">Perfil</a>
      <a class="text-xs px-2 py-1 rounded bg-blue-600 text-white" href="{{ route('admin.appointments.index') }}">Atender</a>
    </div>
  </div>
@empty
  <p class="text-sm text-slate-500">Sin citas para este día.</p>
@endforelse
