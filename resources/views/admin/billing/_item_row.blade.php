@php
  /** @var int $rowIndex */
@endphp

<tr class="item-row border-b hover:bg-slate-50 transition-colors">
  {{-- Servicio --}}
  <td class="px-4 py-3">
    <select
      name="items[{{ $rowIndex }}][service_id]"
      class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      onchange="onServiceChange(this, {{ $rowIndex }})"
    >
      <option value="">— Selecciona servicio —</option>
      @foreach($services as $s)
        <option value="{{ $s->id }}" data-price="{{ $s->price }}">
          {{ $s->name }}
        </option>
      @endforeach
    </select>

    <input
      type="text"
      name="items[{{ $rowIndex }}][description]"
      placeholder="Descripción del servicio..."
      class="mt-2 w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    >
  </td>

  {{-- Odontólogo --}}
  <td class="px-4 py-3">
    <select
      name="items[{{ $rowIndex }}][dentist_id]"
      class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      onchange="loadAvailabilityForRow({{ $rowIndex }})"
    >
      <option value="">— Selecciona —</option>
      @foreach($dentists as $d)
        <option value="{{ $d->id }}">{{ $d->name }}</option>
      @endforeach
    </select>
  </td>

  {{-- Fecha --}}
  <td class="px-4 py-3 text-center">
    <input
  type="date"
  name="items[{{ $rowIndex }}][date]"
  value="{{ now()->toDateString() }}"
  class="w-full border border-slate-300 rounded-lg px-3 py-2 text-center focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
  min="{{ now()->toDateString() }}"
  onchange="loadAvailabilityForRow({{ $rowIndex }})"
/>
  </td>

  {{-- Hora --}}
  <td class="px-4 py-3 text-center">
    <select
      name="items[{{ $rowIndex }}][start_time]"
      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-center focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    >
      <option value="">— Horario —</option>
    </select>
    <div class="text-[11px] text-slate-500 mt-1" id="row-{{ $rowIndex }}-slots-hint"></div>
  </td>

  {{-- Cantidad (forzamos 1 para no duplicar cita misma hora) --}}
  <td class="px-4 py-3">
    <input
      type="number"
      name="items[{{ $rowIndex }}][quantity]"
      value="1"
      min="1"
      max="1"
      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-center focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      oninput="recalcRow(this)"
    >
  </td>

  {{-- Precio unitario --}}
  <td class="px-4 py-3">
    <input
      type="number"
      name="items[{{ $rowIndex }}][unit_price]"
      value="0.00"
      min="0"
      step="0.01"
      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-right focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      oninput="recalcRow(this)"
    >
  </td>

  {{-- Total --}}
  <td class="px-4 py-3 text-right font-semibold text-blue-600">
    <span class="row-total">0.00</span>
    <div class="row-total-display text-sm font-normal text-slate-500">Bs 0.00</div>
  </td>

  {{-- Acciones --}}
  <td class="px-4 py-3 text-right">
    <button
      type="button"
      class="btn bg-red-600 text-white hover:bg-red-700 flex items-center gap-1"
      onclick="removeItemRow(this)"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
      </svg>
      Eliminar
    </button>
  </td>
</tr>
