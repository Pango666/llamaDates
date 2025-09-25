@extends('layouts.app')
@section('title','Facturar visita')

@section('header-actions')
  <a href="{{ route('admin.appointments.show', $appointment) }}" class="btn btn-ghost">Volver a la cita</a>
@endsection

@section('content')
  <div class="card">
    <h3 class="font-semibold mb-3">Factura de la visita</h3>

    <div class="grid md:grid-cols-3 gap-3 mb-4 text-sm">
      <div>
        <div class="text-slate-500">Paciente</div>
        <div class="font-medium">{{ $appointment->patient->last_name }}, {{ $appointment->patient->first_name }}</div>
      </div>
      <div>
        <div class="text-slate-500">Servicio</div>
        <div class="font-medium">{{ $appointment->service->name }}</div>
      </div>
      <div>
        <div class="text-slate-500">Fecha y hora</div>
        <div class="font-medium">
          {{ \Illuminate\Support\Carbon::parse($appointment->date)->toDateString() }}
          · {{ \Illuminate\Support\Str::substr($appointment->start_time,0,5) }}–{{ \Illuminate\Support\Str::substr($appointment->end_time,0,5) }}
        </div>
      </div>
    </div>

    <form method="post" action="{{ route('admin.invoices.storeFromAppointment', $appointment) }}" class="space-y-4">
      @csrf

      <input type="hidden" name="patient_id" value="{{ $appointment->patient_id }}">
      <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">

      <div class="grid md:grid-cols-3 gap-3">
        <div>
          <label class="block text-xs text-slate-500 mb-1">Descuento (Bs)</label>
          <input type="number" step="0.01" min="0" name="discount" class="w-full border rounded px-3 py-2" value="0">
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1">Impuesto %</label>
          <input type="number" step="0.01" min="0" max="100" name="tax_percent" class="w-full border rounded px-3 py-2" value="0">
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1">Notas</label>
          <input type="text" name="notes" class="w-full border rounded px-3 py-2" placeholder="Opcional">
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="border-b">
            <tr class="text-left">
              <th class="px-3 py-2">Descripción</th>
              <th class="px-3 py-2">Cant.</th>
              <th class="px-3 py-2">P. Unit.</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="px-3 py-2">
                <input type="hidden" name="items[0][service_id]" value="{{ $appointment->service_id }}">
                <input type="text" name="items[0][description]" class="w-full border rounded px-2 py-1"
                       value="{{ $appointment->service->name }}">
              </td>
              <td class="px-3 py-2">
                <input type="number" name="items[0][quantity]" class="w-24 border rounded px-2 py-1" value="1" min="1">
              </td>
              <td class="px-3 py-2">
                <input type="number" step="0.01" name="items[0][unit_price]" class="w-32 border rounded px-2 py-1"
                       value="{{ $appointment->service->price ?? '' }}" placeholder="0.00">
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      {{-- Pago inmediato opcional --}}
      <div class="grid md:grid-cols-3 gap-3">
        <div>
          <label class="block text-xs text-slate-500 mb-1">Registrar pago ahora (opcional)</label>
          <input type="number" step="0.01" min="0" name="pay_amount" class="w-full border rounded px-3 py-2" placeholder="0.00">
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1">Método</label>
          <select name="pay_method" class="w-full border rounded px-3 py-2">
            <option value="">—</option>
            <option value="cash">Efectivo</option>
            <option value="card">Tarjeta</option>
            <option value="transfer">Transferencia</option>
            <option value="wallet">Billetera</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1">Referencia</label>
          <input type="text" name="pay_reference" class="w-full border rounded px-3 py-2" placeholder="Nro. voucher / tx">
        </div>
      </div>

      <div class="pt-2">
        <button class="btn btn-primary">Crear factura</button>
      </div>
    </form>
  </div>
@endsection
