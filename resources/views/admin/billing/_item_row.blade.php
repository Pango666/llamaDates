@php
  // Variables esperadas:
  // $rowIndex, $services, $item (opcional en edit)
  $desc  = old("items.$rowIndex.description", $item->description ?? '');
  $sid   = old("items.$rowIndex.service_id",  $item->service_id ?? '');
  $qty   = old("items.$rowIndex.quantity",    $item->quantity ?? 1);
  $unit  = old("items.$rowIndex.unit_price",  $item->unit_price ?? 0);
@endphp
<tr class="item-row border-b">
  <td class="px-2 py-2">
    <select name="items[{{ $rowIndex }}][service_id]" class="border rounded px-2 py-1"
            onchange="onServiceChange(this)">
      <option value="">— Servicio —</option>
      @foreach($services as $s)
        <option value="{{ $s->id }}" data-price="{{ $s->price }}" @selected($sid==$s->id)>
          {{ $s->name }}
        </option>
      @endforeach
    </select>
    <input type="text" name="items[{{ $rowIndex }}][description]" value="{{ $desc }}"
           placeholder="Descripción"
           class="mt-1 w-full border rounded px-2 py-1">
  </td>
  <td class="px-2 py-2">
    <input type="number" name="items[{{ $rowIndex }}][quantity]" value="{{ $qty }}" min="1"
           class="w-20 border rounded px-2 py-1" oninput="recalcRow(this)">
  </td>
  <td class="px-2 py-2">
    <input type="number" name="items[{{ $rowIndex }}][unit_price]" value="{{ $unit }}" min="0" step="0.01"
           class="w-28 border rounded px-2 py-1" oninput="recalcRow(this)">
  </td>
  <td class="px-2 py-2 w-28 text-right">
    <span class="row-total">{{ number_format(($qty * $unit),2,'.','') }}</span>
  </td>
  <td class="px-2 py-2 text-right">
    <button type="button" class="btn btn-danger" onclick="removeItemRow(this)">Eliminar</button>
  </td>
</tr>
