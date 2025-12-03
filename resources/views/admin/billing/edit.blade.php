@extends('layouts.app')
@section('title','Editar factura ' . $invoice->number)

@section('header-actions')
  <a href="{{ route('admin.billing.show',$invoice) }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver
  </a>
@endsection

@section('content')
<form method="post" action="{{ route('admin.billing.update',$invoice) }}" class="space-y-4" id="invoice-form">
  @csrf @method('PUT')

  <div class="card">
    <h3 class="font-semibold mb-3">Datos</h3>
    <div class="grid gap-4 md:grid-cols-3">
      <div class="md:col-span-2">
        <label class="block text-xs text-slate-500 mb-1">Paciente *</label>
        <select name="patient_id" class="w-full border rounded px-3 py-2" required>
          @foreach($patients as $p)
            <option value="{{ $p->id }}" @selected(old('patient_id',$invoice->patient_id)==$p->id)>
              {{ $p->first_name }} {{ $p->last_name }}
            </option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Notas</label>
        <input type="text" name="notes" value="{{ old('notes',$invoice->notes) }}" class="w-full border rounded px-3 py-2">
      </div>
    </div>
  </div>

  <div class="card p-0 overflow-x-auto">
    <div class="p-4 flex items-center justify-between">
      <h3 class="font-semibold">Ítems</h3>
      <button type="button" class="btn btn-ghost" onclick="addItemRow()">+ Agregar</button>
    </div>
    <table class="min-w-full text-sm">
      <thead class="bg-white border-y">
        <tr class="text-left">
          <th class="px-2 py-2">Servicio / Descripción</th>
          <th class="px-2 py-2">Cant.</th>
          <th class="px-2 py-2">P. Unit.</th>
          <th class="px-2 py-2 text-right">Total</th>
          <th class="px-2 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody id="items-body">
        @php $rowIndex = 0; @endphp
        @foreach($invoice->items as $it)
          @include('admin.billing._item_row', ['rowIndex'=>$rowIndex,'services'=>$services,'item'=>$it])
          @php $rowIndex++; @endphp
        @endforeach
      </tbody>
      <tfoot class="bg-slate-50">
        <tr>
          <td colspan="3" class="px-2 py-2 text-right">Descuento</td>
          <td class="px-2 py-2">
            <input type="number" name="discount" value="{{ old('discount',$invoice->discount) }}" min="0" step="0.01"
                   class="w-28 border rounded px-2 py-1" oninput="recalcTotals()">
          </td>
          <td></td>
        </tr>
        <tr>
          <td colspan="3" class="px-2 py-2 text-right">Impuesto %</td>
          <td class="px-2 py-2">
            <input type="number" name="tax_percent" value="{{ old('tax_percent',$invoice->tax_percent) }}" min="0" max="100" step="0.01"
                   class="w-28 border rounded px-2 py-1" oninput="recalcTotals()">
          </td>
          <td></td>
        </tr>
        <tr>
          <td colspan="3" class="px-2 py-2 text-right font-semibold">Total</td>
          <td class="px-2 py-2 text-right font-semibold"><span id="grand-total">0.00</span></td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div class="flex items-center gap-2">
    <button class="btn btn-primary">Guardar cambios</button>
  </div>
</form>

<script>
  let rowIndex = {{ $rowIndex ?? 0 }};

  // mismas funciones que en create:
  function onServiceChange(sel){const opt=sel.options[sel.selectedIndex];const price=parseFloat(opt.getAttribute('data-price')||'0');const tr=sel.closest('tr');if(price>0){tr.querySelector('input[name*="[unit_price]"]').value=price.toFixed(2);const descInput=tr.querySelector('input[name*="[description]"]');if(!descInput.value)descInput.value=opt.text.trim();}recalcRow(sel);}
  function recalcRow(el){const tr=el.closest('tr');const qty=parseFloat(tr.querySelector('input[name*="[quantity]"]').value||'0');const unit=parseFloat(tr.querySelector('input[name*="[unit_price]"]').value||'0');tr.querySelector('.row-total').textContent=(qty*unit).toFixed(2);recalcTotals();}
  function recalcTotals(){let sub=0;document.querySelectorAll('#items-body .row-total').forEach(s=>sub+=parseFloat(s.textContent));const discount=parseFloat(document.querySelector('input[name="discount"]').value||'0');const taxp=parseFloat(document.querySelector('input[name="tax_percent"]').value||'0');const after=Math.max(0,sub-discount);const tax=after*(taxp/100);document.getElementById('grand-total').textContent=(after+tax).toFixed(2);}
  function removeItemRow(btn){btn.closest('tr').remove();recalcTotals();}
  function addItemRow(){rowIndex++;const services=@json($services->map(fn($s)=>['id'=>$s->id,'name'=>$s->name,'price'=>$s->price]));const tr=document.createElement('tr');tr.className='item-row border-b';tr.innerHTML=`<td class="px-2 py-2"><select name="items[${rowIndex}][service_id]" class="border rounded px-2 py-1" onchange="onServiceChange(this)"><option value="">— Servicio —</option>${services.map(s=>`<option value="${s.id}" data-price="${s.price}">${s.name}</option>`).join('')}</select><input type="text" name="items[${rowIndex}][description]" placeholder="Descripción" class="mt-1 w-full border rounded px-2 py-1"></td><td class="px-2 py-2"><input type="number" name="items[${rowIndex}][quantity]" value="1" min="1" class="w-20 border rounded px-2 py-1" oninput="recalcRow(this)"></td><td class="px-2 py-2"><input type="number" name="items[${rowIndex}][unit_price]" value="0.00" min="0" step="0.01" class="w-28 border rounded px-2 py-1" oninput="recalcRow(this)"></td><td class="px-2 py-2 w-28 text-right"><span class="row-total">0.00</span></td><td class="px-2 py-2 text-right"><button type="button" class="btn btn-danger" onclick="removeItemRow(this)">Eliminar</button></td>`;document.getElementById('items-body').appendChild(tr);}
  recalcTotals();
</script>
@endsection
