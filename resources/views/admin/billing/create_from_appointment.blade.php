@extends('layouts.app')
@section('title', 'Cobrar Cita')

@section('header-actions')
  <a href="{{ route('admin.appointments.show', $appointment) }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a la Cita
  </a>
@endsection

@section('content')
  @php
    // Traer servicios si por alguna razón no vinieron del controlador
    /** @var \Illuminate\Support\Collection|\App\Models\Service[] $services */
    $services = $services ?? \App\Models\Service::orderBy('name')->get();

    // Total estimado de insumos usados en esta cita
    $suppliesTotal = \App\Models\AppointmentSupply::where('appointment_id', $appointment->id)
      ->selectRaw('COALESCE(SUM(qty * COALESCE(unit_cost_at_issue,0)),0) as total')
      ->value('total');
  @endphp

  <div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Cobrar Cita
        </h1>
        <p class="text-sm text-slate-600 mt-1">
          Genere el recibo para la cita del paciente. Los insumos usados se sumarán automáticamente
          como un ítem adicional en el recibo.
        </p>
      </div>

      {{-- Mini resumen de insumos (si hay) --}}
      @if($suppliesTotal > 0)
        <div class="mt-4 px-3 py-2 rounded-lg bg-blue-50 border border-blue-100 text-xs text-blue-800 flex items-start gap-2">
          <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div>
            <span class="font-medium">Insumos utilizados:</span>
            esta cita tiene insumos registrados por un total aproximado de
            <span class="font-semibold">Bs {{ number_format($suppliesTotal, 2) }}</span>,
            que se agregarán como un renglón adicional en el recibo.
          </div>
        </div>
      @endif
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
            Servicio principal
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
              {{ \Illuminate\Support\Str::substr($appointment->start_time, 0, 5) }}
              –
              {{ \Illuminate\Support\Str::substr($appointment->end_time, 0, 5) }}
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Formulario de Factura --}}
    <div class="card">
      <form method="post" action="{{ route('admin.invoices.storeFromAppointment', $appointment) }}">
        @csrf

        {{-- Estos hidden no son estrictamente necesarios para el controlador, pero no molestan --}}
        <input type="hidden" name="patient_id" value="{{ $appointment->patient_id }}">
        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">

        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
          </svg>
          Detalles del recibo
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

        {{-- Servicios a cobrar --}}
        <div class="mb-6">
          <div class="flex items-center justify-between mb-2">
            <h4 class="font-semibold text-slate-800">Servicios a cobrar</h4>
            <button
              type="button"
              id="invoice_add_service"
              class="btn btn-ghost text-xs border border-slate-300 hover:bg-slate-100"
            >
              + Agregar servicio
            </button>
          </div>
          <p class="text-xs text-slate-500 mb-3">
            Puedes agregar uno o varios servicios. Cada fila se reflejará como un ítem en el recibo.
          </p>

          <div class="overflow-x-auto">
            <table class="w-full text-sm" id="invoice_items_table">
              <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                  <th class="px-4 py-3 font-semibold text-slate-700">Servicio</th>
                  <th class="px-4 py-3 font-semibold text-slate-700">Descripción en recibo</th>
                  <th class="px-4 py-3 font-semibold text-slate-700 text-center">Cantidad</th>
                  <th class="px-4 py-3 font-semibold text-slate-700 text-right">Precio Unitario</th>
                  <th class="px-4 py-3 font-semibold text-slate-700 text-right"></th>
                </tr>
              </thead>
              <tbody id="invoice_items_body">
                {{-- Fila inicial (índice 0) --}}
                <tr class="border-b hover:bg-slate-50 transition-colors invoice-item-row">
                  <td class="px-4 py-3">
                    <select
                      name="items[0][service_id]"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors invoice-service"
                    >
                      @foreach($services as $service)
                        <option
                          value="{{ $service->id }}"
                          data-price="{{ number_format($service->price ?? 0, 2, '.', '') }}"
                          data-name="{{ $service->name }}"
                          {{ $service->id == $appointment->service_id ? 'selected' : '' }}
                        >
                          {{ $service->name }}
                          @if(!is_null($service->price))
                            (Bs {{ number_format($service->price, 2) }})
                          @endif
                        </option>
                      @endforeach
                    </select>
                  </td>
                  <td class="px-4 py-3">
                    <input
                      type="text"
                      name="items[0][description]"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors invoice-description"
                      value="{{ $appointment->service->name }}"
                    >
                  </td>
                  <td class="px-4 py-3">
                    <input
                      type="number"
                      name="items[0][quantity]"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-center text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors invoice-qty"
                      value="1"
                      min="1"
                    >
                  </td>
                  <td class="px-4 py-3">
                    <input
                      type="number"
                      step="0.01"
                      name="items[0][unit_price]"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-right text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors invoice-unit-price"
                      value="{{ $appointment->service->price ?? '' }}"
                      placeholder="0.00"
                    >
                  </td>
                  <td class="px-4 py-3 text-right">
                    {{-- La primera fila no se puede borrar para evitar quedar sin items --}}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        {{-- Info de insumos --}}
        @if($suppliesTotal > 0)
          <div class="mb-6 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-xs text-slate-700">
            <p class="font-medium mb-1">Insumos de la cita</p>
            <p>
              En el recibo se añadirá automáticamente un ítem:
              <span class="italic">"Insumos utilizados (cita #{{ $appointment->id }})"</span>
              por un total de
              <span class="font-semibold">Bs {{ number_format($suppliesTotal, 2) }}</span>.
            </p>
          </div>
        @endif

        {{-- Pago Inmediato Opcional --}}
        <div class="border-t border-slate-200 pt-6">
          <h4 class="font-semibold text-slate-800 mb-2 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
            </svg>
            Pago Inmediato (Opcional)
          </h4>
          <p class="text-xs text-slate-500 mb-4">
            Usa esta sección si el paciente paga ahora mismo (total o parcial).
            Si el monto cubre el total calculado de la factura, el recibo quedará marcado como <strong>pagado</strong>.
            Si no lo cubre, se registrará como pago parcial y podrás completar el saldo desde la pantalla de detalle del recibo.
          </p>

          <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-700">Monto a Pagar Ahora</label>
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
          <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Generar Recibo
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Template para nuevas filas de servicio --}}
  <template id="invoice_item_row_template">
    <tr class="border-b hover:bg-slate-50 transition-colors invoice-item-row">
      <td class="px-4 py-3">
        <select
          name="items[__INDEX__][service_id]"
          class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors invoice-service"
        >
          @foreach($services as $service)
            <option
              value="{{ $service->id }}"
              data-price="{{ number_format($service->price ?? 0, 2, '.', '') }}"
              data-name="{{ $service->name }}"
            >
              {{ $service->name }}
              @if(!is_null($service->price))
                (Bs {{ number_format($service->price, 2) }})
              @endif
            </option>
          @endforeach
        </select>
      </td>
      <td class="px-4 py-3">
        <input
          type="text"
          name="items[__INDEX__][description]"
          class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors invoice-description"
          value=""
        >
      </td>
      <td class="px-4 py-3">
        <input
          type="number"
          name="items[__INDEX__][quantity]"
          class="w-full border border-slate-300 rounded-lg px-3 py-2 text-center text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors invoice-qty"
          value="1"
          min="1"
        >
      </td>
      <td class="px-4 py-3">
        <input
          type="number"
          step="0.01"
          name="items[__INDEX__][unit_price]"
          class="w-full border border-slate-300 rounded-lg px-3 py-2 text-right text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors invoice-unit-price"
          value=""
          placeholder="0.00"
        >
      </td>
      <td class="px-4 py-3 text-right">
        <button
          type="button"
          class="text-xs text-rose-600 hover:text-rose-700 invoice-remove-row"
        >
          Quitar
        </button>
      </td>
    </tr>
  </template>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const itemsBody        = document.getElementById('invoice_items_body');
    const addServiceBtn    = document.getElementById('invoice_add_service');
    const rowTemplate      = document.getElementById('invoice_item_row_template');
    let   currentIndex     = 1; // Ya existe la fila 0

    if (!itemsBody || !rowTemplate) return;

    // Sincroniza descripción y precio con el servicio seleccionado
    function bindRowEvents(tr) {
      const serviceSelect  = tr.querySelector('.invoice-service');
      const descInput      = tr.querySelector('.invoice-description');
      const unitPriceInput = tr.querySelector('.invoice-unit-price');
      const qtyInput       = tr.querySelector('.invoice-qty');
      const removeBtn      = tr.querySelector('.invoice-remove-row');

      if (serviceSelect) {
        function syncFromSelected() {
          const opt = serviceSelect.options[serviceSelect.selectedIndex];
          if (!opt) return;

          const price = opt.dataset.price || '';
          const name  = opt.dataset.name  || opt.textContent.trim();

          // Si la descripción está vacía, la rellenamos con el nombre del servicio
          if (!descInput.value.trim()) {
            descInput.value = name;
          }

          if (price) {
            unitPriceInput.value = price;
          }

          if (!qtyInput.value || parseFloat(qtyInput.value) <= 0) {
            qtyInput.value = 1;
          }
        }

        serviceSelect.addEventListener('change', syncFromSelected);
        // Llamar una vez al crear la fila
        syncFromSelected();
      }

      if (removeBtn) {
        removeBtn.addEventListener('click', function () {
          const rows = itemsBody.querySelectorAll('.invoice-item-row');
          if (rows.length <= 1) return; // No dejar la tabla sin filas
          tr.remove();
        });
      }
    }

    // Vincular eventos a la fila inicial (índice 0)
    const firstRow = itemsBody.querySelector('.invoice-item-row');
    if (firstRow) {
      bindRowEvents(firstRow);
    }

    // Agregar nueva fila
    if (addServiceBtn) {
      addServiceBtn.addEventListener('click', function () {
        const html = rowTemplate.innerHTML.replace(/__INDEX__/g, currentIndex);
        const wrapper = document.createElement('tbody'); // contenedor temporal
        wrapper.innerHTML = html.trim();
        const newRow = wrapper.firstElementChild;
        itemsBody.appendChild(newRow);
        bindRowEvents(newRow);
        currentIndex++;
      });
    }
  });
</script>
@endpush
