@extends('layouts.app')
@section('title', 'Nueva Factura')

@section('header-actions')
  <a href="{{ route('admin.billing') }}" class="btn btn-ghost flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Facturas
  </a>
@endsection

@section('content')
  <div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Crear Nueva Factura
        </h1>
        <p class="text-sm text-slate-600 mt-1">Complete la información de la factura y agregue los servicios correspondientes.</p>
      </div>
    </div>

    <form method="post" action="{{ route('admin.billing.store') }}" id="invoice-form">
      @csrf

      {{-- Datos de la Factura --}}
      <div class="card mb-6">
        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          Datos de la Factura
        </h3>
        
        <div class="grid gap-4 md:grid-cols-3">
          <div class="md:col-span-2 space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Paciente <span class="text-red-500">*</span>
            </label>
            <select 
              name="patient_id" 
              class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
              required
            >
              <option value="">— Selecciona un paciente —</option>
              @foreach($patients as $patient)
                <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>
                  {{ $patient->first_name }} {{ $patient->last_name }}
                  @if($patient->email) · {{ $patient->email }} @endif
                </option>
              @endforeach
            </select>
          </div>
          
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Notas
            </label>
            <input 
              type="text" 
              name="notes" 
              value="{{ old('notes') }}" 
              class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
              placeholder="Notas adicionales..."
            >
          </div>
        </div>
      </div>

      {{-- Ítems de la Factura --}}
      <div class="card mb-6">
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-200">
          <h3 class="font-semibold text-slate-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Servicios y Productos
          </h3>
          <button 
            type="button" 
            class="btn bg-green-600 text-white hover:bg-green-700 flex items-center gap-2"
            onclick="addItemRow()"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Agregar Servicio
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr class="text-left">
                <th class="px-4 py-3 font-semibold text-slate-700">Servicio / Descripción</th>
                <th class="px-4 py-3 font-semibold text-slate-700 text-center">Cantidad</th>
                <th class="px-4 py-3 font-semibold text-slate-700 text-right">Precio Unitario</th>
                <th class="px-4 py-3 font-semibold text-slate-700 text-right">Total</th>
                <th class="px-4 py-3 font-semibold text-slate-700 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody id="items-body">
              @php $rowIndex = 0; @endphp
              @include('admin.billing._item_row', ['rowIndex' => $rowIndex, 'services' => $services])
            </tbody>
          </table>
        </div>

        {{-- Totales --}}
        <div class="mt-6 pt-6 border-t border-slate-200">
          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 max-w-2xl ml-auto">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-700">Descuento (Bs)</label>
              <input 
                type="number" 
                name="discount" 
                value="{{ old('discount', 0) }}" 
                min="0" 
                step="0.01"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                oninput="recalcTotals()"
              >
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-medium text-slate-700">Impuesto %</label>
              <input 
                type="number" 
                name="tax_percent" 
                value="{{ old('tax_percent', 0) }}" 
                min="0" 
                max="100" 
                step="0.01"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                oninput="recalcTotals()"
              >
            </div>
            
            <div class="lg:col-span-2 space-y-2">
              <label class="block text-sm font-medium text-slate-700">Total de la Factura</label>
              <div class="bg-slate-50 border border-slate-300 rounded-lg px-4 py-3">
                <div class="text-2xl font-bold text-blue-600 text-right">
                  Bs <span id="grand-total">0.00</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Acciones --}}
      <div class="flex items-center gap-4 pt-6 border-t border-slate-200">
        <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Guardar y Emitir Factura
        </button>
        <div class="flex-1">
          <p class="text-sm text-slate-600">
            La factura se creará con estado <strong>"Emitida"</strong> y estará lista para procesar pagos.
          </p>
        </div>
        <a href="{{ route('admin.billing') }}" class="btn btn-ghost flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Cancelar
        </a>
      </div>
    </form>
  </div>

  {{-- JavaScript (se mantiene similar pero adaptado) --}}
  @push('scripts')
  <script>
    let rowIndex = {{ $rowIndex }};

    function onServiceChange(sel) {
      const opt = sel.options[sel.selectedIndex];
      const price = parseFloat(opt.getAttribute('data-price') || '0');
      const tr = sel.closest('tr');
      if (price > 0) {
        tr.querySelector('input[name*="[unit_price]"]').value = price.toFixed(2);
        const descInput = tr.querySelector('input[name*="[description]"]');
        if (!descInput.value) descInput.value = opt.text.trim();
      }
      recalcRow(sel);
    }

    function recalcRow(el) {
      const tr = el.closest('tr');
      const qty = parseFloat(tr.querySelector('input[name*="[quantity]"]').value || '0');
      const unit = parseFloat(tr.querySelector('input[name*="[unit_price]"]').value || '0');
      const total = (qty * unit).toFixed(2);
      tr.querySelector('.row-total').textContent = total;
      tr.querySelector('.row-total-display').textContent = 'Bs ' + total;
      recalcTotals();
    }

    function recalcTotals() {
      let sub = 0;
      document.querySelectorAll('#items-body .row-total').forEach(s => sub += parseFloat(s.textContent));
      const discount = parseFloat(document.querySelector('input[name="discount"]').value || '0');
      const taxp = parseFloat(document.querySelector('input[name="tax_percent"]').value || '0');
      const after = Math.max(0, sub - discount);
      const tax = after * (taxp/100);
      const total = (after + tax).toFixed(2);
      document.getElementById('grand-total').textContent = total;
    }

    function removeItemRow(btn) {
      const tr = btn.closest('tr');
      tr.remove();
      recalcTotals();
    }

    function addItemRow() {
      rowIndex++;
      const services = @json($services->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'price' => $s->price]));
      const tr = document.createElement('tr');
      tr.className = 'item-row border-b hover:bg-slate-50 transition-colors';
      tr.innerHTML = `
        <td class="px-4 py-3">
          <select name="items[${rowIndex}][service_id]" 
                  class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                  onchange="onServiceChange(this)">
            <option value="">— Selecciona servicio —</option>
            ${services.map(s => `<option value="${s.id}" data-price="${s.price}">${s.name}</option>`).join('')}
          </select>
          <input type="text" 
                 name="items[${rowIndex}][description]" 
                 placeholder="Descripción del servicio..."
                 class="mt-2 w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
        </td>
        <td class="px-4 py-3">
          <input type="number" 
                 name="items[${rowIndex}][quantity]" 
                 value="1" 
                 min="1" 
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                 oninput="recalcRow(this)">
        </td>
        <td class="px-4 py-3">
          <input type="number" 
                 name="items[${rowIndex}][unit_price]" 
                 value="0.00" 
                 min="0" 
                 step="0.01" 
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                 oninput="recalcRow(this)">
        </td>
        <td class="px-4 py-3 text-right font-semibold text-blue-600">
          <span class="row-total">0.00</span>
          <div class="row-total-display text-sm font-normal text-slate-500">Bs 0.00</div>
        </td>
        <td class="px-4 py-3 text-right">
          <button type="button" 
                  class="btn bg-red-600 text-white hover:bg-red-700 flex items-center gap-1"
                  onclick="removeItemRow(this)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Eliminar
          </button>
        </td>
      `;
      document.getElementById('items-body').appendChild(tr);
    }

    // Inicializar
    document.addEventListener('DOMContentLoaded', recalcTotals);
  </script>
  @endpush
@endsection