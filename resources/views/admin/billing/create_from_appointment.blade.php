@extends('layouts.app')
@section('title', 'Facturar Cita')

@section('header-actions')
  <a href="{{ route('admin.appointments.show', $appointment) }}"
     class="btn btn-ghost flex items-center gap-2 border border-blue-200 text-blue-700 hover:bg-blue-50 hover:text-blue-800">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a la Cita
  </a>
  <a href="{{ route('admin.appointments.show', $appointment) }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a la Cita
  </a>
@endsection

@section('content')
  <div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Facturar Cita
        </h1>
        <p class="text-sm text-slate-600 mt-1">Genere la factura para la cita del paciente.</p>
      </div>
    </div>

    {{-- Información de la Cita --}}
    <div class="card mb-6">
      <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        Información de la Cita
      </h3>

      <div class="grid md:grid-cols-3 gap-6">
        <div class="space-y-1">
          <div class="text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Paciente
          </div>
          <div class="text-lg font-semibold text-slate-800">
            {{ $appointment->patient->last_name }}, {{ $appointment->patient->first_name }}
          </div>
        </div>

        <div class="space-y-1">
          <div class="text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Servicio
          </div>
          <div class="text-lg font-semibold text-blue-600">
            {{ $appointment->service->name }}
          </div>
        </div>

        <div class="space-y-1">
          <div class="text-sm font-medium text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Fecha y Hora
          </div>
          <div class="text-lg font-semibold text-slate-800">
            {{ \Illuminate\Support\Carbon::parse($appointment->date)->format('d/m/Y') }}
            <div class="text-sm font-normal text-slate-600">
              {{ \Illuminate\Support\Str::substr($appointment->start_time, 0, 5) }} – {{ \Illuminate\Support\Str::substr($appointment->end_time, 0, 5) }}
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Formulario de Factura --}}
    <div class="card">
      <form method="post" action="{{ route('admin.invoices.storeFromAppointment', $appointment) }}">
        @csrf

        <input type="hidden" name="patient_id" value="{{ $appointment->patient_id }}">
        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">

        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
          </svg>
          Detalles de la Factura
        </h3>

        {{-- Configuración de Factura --}}
        <div class="grid md:grid-cols-3 gap-4 mb-6">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700">Descuento (Bs)</label>
            <input
              type="number"
              step="0.01"
              min="0"
              name="discount"
              value="0"
              class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            >
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700">Impuesto %</label>
            <input
              type="number"
              step="0.01"
              min="0"
              max="100"
              name="tax_percent"
              value="0"
              class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            >
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700">Notas</label>
            <input
              type="text"
              name="notes"
              class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
              placeholder="Notas opcionales..."
            >
          </div>
        </div>

        {{-- Servicio --}}
        <div class="mb-6">
          <h4 class="font-semibold text-slate-800 mb-3">Servicio a Facturar</h4>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                  <th class="px-4 py-3 font-semibold text-slate-700">Descripción</th>
                  <th class="px-4 py-3 font-semibold text-slate-700 text-center">Cantidad</th>
                  <th class="px-4 py-3 font-semibold text-slate-700 text-right">Precio Unitario</th>
                </tr>
              </thead>
              <tbody>
                <tr class="border-b hover:bg-slate-50 transition-colors">
                  <td class="px-4 py-3">
                    <input type="hidden" name="items[0][service_id]" value="{{ $appointment->service_id }}">
                    <input
                      type="text"
                      name="items[0][description]"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                      value="{{ $appointment->service->name }}"
                    >
                  </td>
                  <td class="px-4 py-3">
                    <input
                      type="number"
                      name="items[0][quantity]"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-center focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                      value="1"
                      min="1"
                    >
                  </td>
                  <td class="px-4 py-3">
                    <input
                      type="number"
                      step="0.01"
                      name="items[0][unit_price]"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-right focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                      value="{{ $appointment->service->price ?? '' }}"
                      placeholder="0.00"
                    >
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        {{-- Pago Inmediato Opcional --}}
        <div class="border-t border-slate-200 pt-6">
          <h4 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
            </svg>
            Pago Inmediato (Opcional)
          </h4>

          <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-700">Monto a Pagar</label>
              <input
                type="number"
                step="0.01"
                min="0"
                name="pay_amount"
                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                placeholder="0.00"
              >
            </div>

            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-700">Método de Pago</label>
              <select
                name="pay_method"
                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
              >
                <option value="">— Selecciona método —</option>
                <option value="cash">Efectivo</option>
                <option value="card">Tarjeta</option>
                <option value="transfer">Transferencia</option>
                <option value="wallet">Billetera Digital</option>
              </select>
            </div>

            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-700">Referencia</label>
              <input
                type="text"
                name="pay_reference"
                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                placeholder="Número de voucher o transacción"
              >
            </div>
          </div>
        </div>

        {{-- Acciones --}}
        <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
          <a href="{{ route('admin.appointments.show', $appointment) }}"
             class="btn btn-ghost border border-slate-300 text-slate-700 hover:bg-slate-100">
            Cancelar
          </a>
          <button type="submit" class="btn btn-primary">
            Generar factura
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
