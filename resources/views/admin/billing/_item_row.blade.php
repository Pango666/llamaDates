@php
  $desc  = old("items.$rowIndex.description", $item->description ?? '');
  $sid   = old("items.$rowIndex.service_id",  $item->service_id ?? '');
  $qty   = old("items.$rowIndex.quantity",    $item->quantity ?? 1);
  $unit  = old("items.$rowIndex.unit_price",  $item->unit_price ?? 0);
  $total = $qty * $unit;
@endphp

<tr class="item-row border-b hover:bg-slate-50 transition-colors">
  <td class="px-4 py-3">
    <select 
      name="items[{{ $rowIndex }}][service_id]" 
      class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      onchange="onServiceChange(this)"
    >
      <option value="">— Selecciona servicio —</option>
      @foreach($services as $service)
        <option 
          value="{{ $service->id }}" 
          data-price="{{ $service->price }}" 
          @selected($sid == $service->id)
        >
          {{ $service->name }}
        </option>
      @endforeach
    </select>
    <input 
      type="text" 
      name="items[{{ $rowIndex }}][description]" 
      value="{{ $desc }}"
      placeholder="Descripción del servicio..."
      class="mt-2 w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
    >
  </td>
  
  <td class="px-4 py-3">
    <input 
      type="number" 
      name="items[{{ $rowIndex }}][quantity]" 
      value="{{ $qty }}" 
      min="1"
      class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      oninput="recalcRow(this)"
    >
  </td>
  
  <td class="px-4 py-3">
    <input 
      type="number" 
      name="items[{{ $rowIndex }}][unit_price]" 
      value="{{ $unit }}" 
      min="0" 
      step="0.01"
      class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
      oninput="recalcRow(this)"
    >
  </td>
  
  <td class="px-4 py-3 text-right">
    <span class="row-total hidden">{{ number_format($total, 2, '.', '') }}</span>
    <div class="font-semibold text-blue-600 row-total-display">
      Bs {{ number_format($total, 2) }}
    </div>
  </td>
  
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