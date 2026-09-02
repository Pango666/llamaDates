@extends('layouts.app')
@section('title','Detalle del plan')

@section('header-actions')
  <a href="{{ route('admin.patients.show', $plan->patient_id) }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver
</a>
  <a href="{{ route('admin.patients.plans.index', $plan->patient_id) }}" class="btn btn-ghost">Ver planes</a>
  <a href="{{ route('admin.plans.print',$plan) }}" class="btn btn-ghost">Imprimir</a>
  <a href="{{ route('admin.plans.pdf',$plan) }}" class="btn btn-ghost">PDF</a>
  <a href="{{ route('admin.plans.invoice.create',$plan) }}" class="btn btn-primary">Cobrar</a>
@endsection

@section('content')
<div class="grid gap-4">

  {{-- Encabezado / meta del plan --}}
  <section class="card">
    <form method="post" action="{{ route('admin.plans.update', $plan) }}" class="grid md:grid-cols-6 gap-3">
      @csrf @method('PUT')

      <div class="md:col-span-3">
        <label class="block text-xs text-slate-500 mb-1">Título Personalizado</label>
        <input name="title" value="{{ old('title',$plan->title) }}" class="w-full border rounded px-3 py-2" required>
      </div>

      <div class="md:col-span-3">
        <label class="block text-xs text-slate-500 mb-1">Servicio / Tratamiento Base</label>
        <select name="service_id" class="w-full border rounded px-3 py-2">
          @foreach($services as $s)
            <option value="{{ $s->id }}" @selected(old('service_id',$plan->service_id)==$s->id)>{{ $s->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="md:col-span-2">
        <label class="block text-xs text-slate-500 mb-1">Odontólogo Asignado</label>
        <select name="dentist_id" class="w-full border rounded px-3 py-2">
          @foreach($dentists as $d)
            <option value="{{ $d->id }}" @selected(old('dentist_id',$plan->dentist_id)==$d->id)>{{ $d->user->name ?? 'Dr. '.$d->id }}</option>
          @endforeach
        </select>
      </div>

      <div class="md:col-span-2">
        <label class="block text-xs text-slate-500 mb-1">Pieza Dental</label>
        <input type="text" name="tooth_code" value="{{ old('tooth_code',$plan->tooth_code) }}" class="w-full border rounded px-3 py-2" placeholder="N/A">
      </div>

      <div class="md:col-span-2">
        <label class="block text-xs text-slate-500 mb-1">Superficie</label>
        <select name="surface" class="w-full border rounded px-3 py-2">
            <option value="" @selected(!$plan->surface)>N/A</option>
            @foreach(['O','M','D','B','L','I'] as $val)
            <option value="{{ $val }}" @selected($plan->surface==$val)>{{ $val }}</option>
            @endforeach
        </select>
      </div>

      <div class="md:col-span-2">
        <label class="block text-xs text-slate-500 mb-1">Estado</label>
        <select name="status" class="w-full border rounded px-3 py-2">
          @foreach(['draft'=>'Borrador','approved'=>'Aprobado','in_progress'=>'En curso','completed'=>'Completado','canceled'=>'Cancelado'] as $val=>$lbl)
            <option value="{{ $val }}" @selected(old('status',$plan->status)===$val)>{{ $lbl }}</option>
          @endforeach
        </select>
      </div>

      <div class="md:col-span-2">
        <label class="block text-xs text-slate-500 mb-1">Costo Estimado (Bs)</label>
        <input type="number" step="0.01" name="estimate_total" value="{{ old('estimate_total', $plan->estimate_total) }}" class="w-full border rounded px-3 py-2" required>
      </div>

      <div class="md:col-span-1">
        <label class="block text-xs text-slate-500 mb-1">Sesiones Estimadas</label>
        <input type="number" name="total_sessions" value="{{ old('total_sessions', $plan->total_sessions) }}" class="w-full border rounded px-3 py-2" min="1" required>
      </div>

      <div class="md:col-span-1">
        <label class="block text-xs text-slate-500 mb-1">Completadas</label>
        <div class="w-full border rounded px-3 py-2 bg-slate-50 text-slate-700 font-semibold text-center">
          {{ $plan->completed_sessions }} / {{ $plan->total_sessions }}
        </div>
      </div>

      <div class="md:col-span-6 bg-slate-50 rounded-lg p-4 border mt-2">
        <h4 class="text-xs font-semibold text-slate-600 mb-2 uppercase tracking-wider">Resumen Financiero</h4>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-xs text-slate-500">Costo Estimado</p>
                <p class="font-semibold text-slate-800">Bs {{ number_format($plan->estimate_total, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Total Pagado</p>
                <p class="font-semibold text-emerald-600">Bs {{ number_format($plan->paid_amount, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Saldo Pendiente</p>
                <p class="font-semibold text-red-600">Bs {{ number_format($plan->balance, 2) }}</p>
            </div>
        </div>
        @if($plan->invoiceLatest)
          <div class="text-center text-xs mt-3">
            Último recibo: <a href="{{ route('admin.invoices.show',$plan->invoiceLatest) }}" class="text-blue-600 hover:underline font-semibold">#{{ $plan->invoiceLatest->number }}</a>
            <span class="ml-2 badge {{ $plan->invoiceLatest->status==='paid'?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700' }}">
              {{ $plan->invoiceLatest->status==='paid'?'Pagada':'Emitida' }}
            </span>
          </div>
        @endif
      </div>

      <div class="flex items-end gap-2 md:col-span-6 mt-2">
        <button type="submit" name="action" value="save" class="btn btn-primary">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Guardar cambios
        </button>
        @if($plan->status === 'draft')
        <button type="submit" name="action" value="approve" class="btn btn-success">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Aprobar plan
        </button>
        @endif
      </div>
    </form>
    <div class="text-xs text-slate-500 mt-4 border-t pt-3 flex justify-between">
      <span>Paciente: <span class="font-medium">{{ $plan->patient?->last_name }}, {{ $plan->patient?->first_name }}</span></span>
      <span>Creado: {{ $plan->created_at?->format('d/m/Y H:i') }}</span>
    </div>
  </section>

  {{-- Progreso del plan (Sesiones programadas) --}}
  <section class="card p-0">
    <div class="p-4 border-b flex justify-between items-center bg-slate-50">
        <h3 class="font-semibold text-slate-800">Sesiones Programadas (Citas)</h3>
        
        @if(in_array($plan->status, ['approved', 'in_progress']) && $plan->completed_sessions < $plan->total_sessions)
        <a href="{{ route('admin.appointments.create', ['patient_id' => $plan->patient_id, 'plan_id' => $plan->id, 'dentist_id' => $plan->dentist_id]) }}" class="btn btn-primary py-1 px-3 text-sm">
            Agendar Sesión {{ $plan->appointments->count() + 1 }}
        </a>
        @elseif($plan->completed_sessions >= $plan->total_sessions)
        <span class="badge bg-emerald-100 text-emerald-700">Tratamiento finalizado</span>
        @endif
    </div>
    
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-white border-b sticky top-0 z-10">
          <tr class="text-left">
            <th class="px-3 py-2"># Sesión</th>
            <th class="px-3 py-2">Fecha y Hora</th>
            <th class="px-3 py-2">Odontólogo</th>
            <th class="px-3 py-2">Estado</th>
            <th class="px-3 py-2 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($plan->appointments as $i => $apt)
            @php
              $badge = match($apt->status){
                'scheduled'   => 'bg-blue-100 text-blue-700',
                'confirmed'   => 'bg-amber-100 text-amber-700',
                'completed','attended' => 'bg-emerald-100 text-emerald-700',
                'canceled'    => 'bg-red-100 text-red-700',
                'no_show'     => 'bg-slate-100 text-slate-700',
                default       => 'bg-slate-100 text-slate-700',
              };
            @endphp
            <tr class="border-b hover:bg-slate-50">
              <td class="px-3 py-2 font-medium">Sesión {{ $i+1 }}</td>
              <td class="px-3 py-2">{{ \Carbon\Carbon::parse($apt->start_time)->format('d/m/Y h:i A') }}</td>
              <td class="px-3 py-2">{{ $apt->dentist->user->name ?? '—' }}</td>
              <td class="px-3 py-2">
                <span class="badge {{ $badge }}">{{ ucfirst($apt->status) }}</span>
              </td>
              <td class="px-3 py-2 text-right">
                  <a href="{{ route('admin.appointments.show', $apt) }}" class="btn btn-ghost text-xs py-1 px-2">Ver cita</a>
              </td>
            </tr>
          @empty
            <tr>
              <td class="px-3 py-6 text-center text-slate-500" colspan="5">Aún no se ha programado ninguna sesión.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
</div>
@endsection
