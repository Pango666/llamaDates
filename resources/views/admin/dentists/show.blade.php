@extends('layouts.app')
@section('title','Perfil de odontólogo')

@section('header-actions')
  <a href="{{ route('admin.dentists') }}" class="btn btn-ghost">Volver</a>
  <a href="{{ route('admin.dentists.edit',$dentist) }}" class="btn btn-ghost">Editar</a>
@endsection

@section('content')
  <div class="grid gap-4 md:grid-cols-3">
    <section class="card md:col-span-1">
      <h3 class="font-semibold mb-3">Datos</h3>
      <dl class="text-sm space-y-2">
        <div>
          <dt class="text-slate-500">Nombre</dt>
          <dd class="font-medium">{{ $dentist->name }}</dd>
        </div>
        <div>
          <dt class="text-slate-500">Especialidad</dt>
          <dd>{{ $dentist->specialty ?: '—' }}</dd>
        </div>
        <div>
          <dt class="text-slate-500">Sillón</dt>
          <dd>{{ $dentist->chair->name ?? '—' }}</dd>
        </div>
        <div>
          <dt class="text-slate-500">Usuario</dt>
          <dd>
            @if($dentist->user)
              {{ $dentist->user->name }} — {{ $dentist->user->email }}
            @else
              —
            @endif
          </dd>
        </div>
        <div>
          <dt class="text-slate-500">Creado</dt>
          <dd>{{ $dentist->created_at?->format('Y-m-d H:i') ?? '—' }}</dd>
        </div>
      </dl>
    </section>

    <section class="card md:col-span-2">
      <h3 class="font-semibold mb-3">Próximas citas</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="border-b">
            <tr class="text-left">
              <th class="px-3 py-2">Fecha</th>
              <th class="px-3 py-2">Hora</th>
              <th class="px-3 py-2">Paciente</th>
              <th class="px-3 py-2">Servicio</th>
              <th class="px-3 py-2">Estado</th>
            </tr>
          </thead>
          <tbody>
            @forelse($upcoming as $a)
              @php
                $badge = [
                  'reserved'   => 'bg-slate-100 text-slate-700',
                  'confirmed'  => 'bg-blue-100 text-blue-700',
                  'in_service' => 'bg-amber-100 text-amber-700',
                  'done'       => 'bg-emerald-100 text-emerald-700',
                  'no_show'    => 'bg-rose-100 text-rose-700',
                  'canceled'   => 'bg-slate-200 text-slate-700 line-through',
                ][$a->status] ?? 'bg-slate-100 text-slate-700';
              @endphp
              <tr class="border-b">
                <td class="px-3 py-2">{{ \Illuminate\Support\Carbon::parse($a->date)->format('Y-m-d') }}</td>
                <td class="px-3 py-2">{{ \Illuminate\Support\Str::substr($a->start_time,0,5) }}–{{ \Illuminate\Support\Str::substr($a->end_time,0,5) }}</td>
                <td class="px-3 py-2">{{ $a->patient->first_name }} {{ $a->patient->last_name }}</td>
                <td class="px-3 py-2">{{ $a->service->name }}</td>
                <td class="px-3 py-2">
                  <span class="badge {{ $badge }}">{{ [
                    'reserved'=>'Reservado','confirmed'=>'Confirmado','in_service'=>'En atención',
                    'done'=>'Atendido','no_show'=>'No asistió','canceled'=>'Cancelado'
                  ][$a->status] ?? $a->status }}</span>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">Sin próximas citas.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>
@endsection
