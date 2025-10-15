@extends('layouts.app')
@section('title','Nuevo movimiento')

@section('content')
  <div class="card">
    <form method="post" action="{{ route('admin.inv.movs.store') }}" class="grid md:grid-cols-3 gap-3">
      @csrf
      <div class="md:col-span-2">
        <label class="text-xs text-slate-500">Producto</label>
        <select name="product_id" class="border rounded px-3 py-2" required>
          @foreach($products as $p)
            <option value="{{ $p->id }}" data-cost="{{ $p->cost_avg }}">{{ $p->name }} ({{ $p->sku }})</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500">Ubicación</label>
        <select name="location_id" class="border rounded px-3 py-2" required>
          @foreach($locations as $l)
            <option value="{{ $l->id }}">{{ $l->name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="text-xs text-slate-500">Tipo</label>
        <select name="type" class="border rounded px-3 py-2" id="typeSel">
          <option value="in">Entrada</option>
          <option value="out">Salida</option>
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500">Cantidad</label>
        <input name="qty" type="number" step="0.0001" min="0.0001" value="1" class="border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="text-xs text-slate-500">Costo unitario (Bs)</label>
        <input name="unit_cost" type="number" step="0.0001" min="0" class="border rounded px-3 py-2" id="costInput" placeholder="Si salida, se usa costo promedio si lo dejas vacío">
      </div>

      <div class="md:col-span-3">
        <label class="text-xs text-slate-500">Notas</label>
        <input name="notes" class="border rounded px-3 py-2 w-full" placeholder="Opcional">
      </div>

      <div class="md:col-span-3">
        <button class="btn">Registrar</button>
        <a href="{{ route('admin.inv.movs.index') }}" class="btn btn-ghost">Cancelar</a>
      </div>
    </form>
  </div>

  <script>
    // ayuda visual: si eliges "Salida" y dejas costo vacío, no pasa nada (será costo promedio)
    const typeSel = document.getElementById('typeSel');
    const costInput = document.getElementById('costInput');
    typeSel?.addEventListener('change', () => {
      if (typeSel.value === 'out') {
        costInput.placeholder = 'Vacío = usa costo promedio actual';
      } else {
        costInput.placeholder = 'Requerido para entrada (si lo dejas 0, no promedia)';
      }
    });
  </script>
@endsection
