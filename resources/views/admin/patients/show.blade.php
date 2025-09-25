@extends('layouts.app')
@section('title','Perfil de paciente')

@section('header-actions')
  <a href="{{ route('admin.patients') }}" class="btn btn-ghost">Volver</a>
  <a href="{{ route('admin.patients.edit',$patient) }}" class="btn btn-ghost">Editar</a>
  <a href="{{ route('admin.patients.record',$patient) }}" class="btn btn-ghost">Historia completa</a>
  <a href="{{ route('admin.odontograms.open',$patient) }}" class="btn btn-ghost">Odontograma</a>
  <a href="{{ route('admin.patients.plans.index',$patient) }}" class="btn btn-ghost">Ver planes</a>
  <a href="{{ route('admin.patients.plans.create',$patient) }}" class="btn btn-ghost">+ Nuevo plan</a>
  <a href="{{ route('admin.appointments.create', ['patient_id'=>$patient->id]) }}" class="btn btn-primary">+ Nueva cita</a>
@endsection

@section('content')
  <div class="grid gap-4 md:grid-cols-3">
    {{-- Datos --}}
    <section class="card md:col-span-1">
      <h3 class="font-semibold mb-3">Datos del paciente</h3>
      <dl class="text-sm space-y-2">
        <div>
          <dt class="text-slate-500">Nombre</dt>
          <dd class="font-medium">{{ $patient->last_name }}, {{ $patient->first_name }}</dd>
        </div>
        <div>
          <dt class="text-slate-500">CI</dt>
          <dd>{{ $patient->ci ?: '—' }}</dd>
        </div>
        <div>
          <dt class="text-slate-500">Nacimiento</dt>
          <dd>
            {{ $patient->birthdate ?: '—' }}
            @if(isset($age)) <span class="text-xs text-slate-500">({{ $age }} años)</span> @endif
          </dd>
        </div>
        <div>
          <dt class="text-slate-500">Email</dt>
          <dd>{{ $patient->email ?: '—' }}</dd>
        </div>
        <div>
          <dt class="text-slate-500">Teléfono</dt>
          <dd>{{ $patient->phone ?: '—' }}</dd>
        </div>
        <div>
          <dt class="text-slate-500">Dirección</dt>
          <dd class="whitespace-pre-line">{{ $patient->address ?: '—' }}</dd>
        </div>
        <div>
          <dt class="text-slate-500">Creado</dt>
          <dd>{{ $patient->created_at?->format('Y-m-d H:i') ?? '—' }}</dd>
        </div>
      </dl>
    </section>

    {{-- Citas recientes --}}
    <section class="card md:col-span-2">
      <div class="flex items-center justify-between mb-2">
        <h3 class="font-semibold">Últimas citas</h3>
        <a href="{{ route('admin.appointments.index', ['patient_id'=>$patient->id]) }}" class="text-sm text-blue-600 hover:underline">Ver todas</a>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="border-b">
            <tr class="text-left">
              <th class="px-3 py-2">Fecha</th>
              <th class="px-3 py-2">Hora</th>
              <th class="px-3 py-2">Servicio</th>
              <th class="px-3 py-2">Odontólogo</th>
              <th class="px-3 py-2">Estado</th>
              <th class="px-3 py-2 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
          @forelse($appointments as $a)
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
              <td class="px-3 py-2">{{ $a->service->name }}</td>
              <td class="px-3 py-2">{{ $a->dentist->name }}</td>
              <td class="px-3 py-2">
                <span class="badge {{ $badge }}">{{ [
                  'reserved'=>'Reservado','confirmed'=>'Confirmado','in_service'=>'En atención',
                  'done'=>'Atendido','no_show'=>'No asistió','canceled'=>'Cancelado'
                ][$a->status] ?? $a->status }}</span>
              </td>
              <td class="px-3 py-2 text-right">
                <a href="{{ route('admin.appointments.show',$a->id) }}" class="btn btn-ghost">Ver</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">Sin citas aún.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>

  {{-- Bloque informativo de planes dentro del content (no fuera) --}}
  <section class="card md:col-span-3 mt-4">
  <div class="flex items-center justify-between mb-2">
    <h3 class="font-semibold">Planes de tratamiento</h3>
    <div class="flex gap-2">
      <a href="{{ route('admin.patients.plans.index',$patient) }}" class="btn btn-ghost">Ver planes</a>
      <a href="{{ route('admin.patients.plans.create',$patient) }}" class="btn btn-primary">+ Nuevo plan</a>
    </div>
  </div>

  @if(isset($plans) && $plans->count())
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="border-b">
          <tr class="text-left">
            <th class="px-3 py-2">Título</th>
            <th class="px-3 py-2">Estado</th>
            <th class="px-3 py-2">Total estimado</th>
            <th class="px-3 py-2">Creado</th>
            <th class="px-3 py-2 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
        @foreach($plans as $p)
          @php
            $badge = [
              'draft'       => 'bg-slate-100 text-slate-700',
              'approved'    => 'bg-blue-100 text-blue-700',
              'in_progress' => 'bg-emerald-100 text-emerald-700',
            ][$p->status] ?? 'bg-slate-100 text-slate-700';
          @endphp
          <tr class="border-b">
            <td class="px-3 py-2">{{ $p->title }}</td>
            <td class="px-3 py-2"><span class="badge {{ $badge }}">{{ str_replace('_',' ', ucfirst($p->status)) }}</span></td>
            <td class="px-3 py-2">Bs {{ number_format($p->estimate_total,2) }}</td>
            <td class="px-3 py-2">{{ $p->created_at?->format('Y-m-d') }}</td>
            <td class="px-3 py-2">
              <div class="flex justify-end gap-2">
                <a href="{{ route('admin.plans.edit',$p) }}" class="btn btn-ghost">Abrir</a>
                <a href="{{ route('admin.plans.print',$p) }}" class="btn btn-ghost">Imprimir</a>
                <a href="{{ route('admin.plans.pdf',$p) }}" class="btn btn-ghost">PDF</a>
                <a href="{{ route('admin.plans.invoice.create',$p) }}" class="btn btn-ghost">Facturar</a>
              </div>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  @else
    <p class="text-sm text-slate-500">
      Aún no hay planes. Crea uno con “+ Nuevo plan”.
    </p>
  @endif
</section>
@endsection
