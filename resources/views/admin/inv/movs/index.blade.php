@extends('layouts.app')
@section('title','Movimientos')

@section('header-actions')
  <a href="{{ route('admin.inv.movs.create') }}" class="btn">+ Nuevo movimiento</a>
@endsection

@section('content')
  <div class="card mb-3">
    <form method="get" class="grid md:grid-cols-6 gap-2">
      <div class="md:col-span-2">
        <label class="text-xs text-slate-500">Producto</label>
        <select name="product_id" class="border rounded px-3 py-2">
          <option value="0">— Todos —</option>
          @foreach($products as $p)
            <option value="{{ $p->id }}" @selected($qProd==$p->id)>{{ $p->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="text-xs text-slate-500">Ubicación</label>
        <select name="location_id" class="border rounded px-3 py-2">
          <option value="0">— Todas —</option>
          @foreach($locations as $l)
            <option value="{{ $l->id }}" @selected($qLoc==$l->id)>{{ $l->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500">Tipo</label>
        <select name="type" class="border rounded px-3 py-2">
          <option value="all" @selected($type==='all')>Todos</option>
          <option value="in"  @selected($type==='in')>Entrada</option>
          <option value="out" @selected($type==='out')>Salida</option>
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500">Desde</label>
        <input type="date" name="from" value="{{ $from }}" class="border rounded px-3 py-2">
      </div>
      <div>
        <label class="text-xs text-slate-500">Hasta</label>
        <input type="date" name="to" value="{{ $to }}" class="border rounded px-3 py-2">
      </div>
      <div class="md:col-span-6">
        <button class="btn">Filtrar</button>
      </div>
    </form>
  </div>

  <div class="card overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="border-b">
        <tr class="text-left">
          <th class="px-3 py-2">Fecha</th>
          <th class="px-3 py-2">Producto</th>
          <th class="px-3 py-2">Ubicación</th>
          <th class="px-3 py-2">Tipo</th>
          <th class="px-3 py-2">Cantidad</th>
          <th class="px-3 py-2">Costo unit. (Bs)</th>
          <th class="px-3 py-2">Total (Bs)</th>
          <th class="px-3 py-2">Notas</th>
        </tr>
      </thead>
      <tbody>
        @foreach($movs as $m)
          <tr class="border-b">
            <td class="px-3 py-2">{{ $m->created_at->format('Y-m-d H:i') }}</td>
            <td class="px-3 py-2">{{ $m->product->name }}</td>
            <td class="px-3 py-2">{{ $m->location->name }}</td>
            <td class="px-3 py-2">
              <span class="badge {{ $m->type==='in'?'bg-blue-100 text-blue-700':'bg-rose-100 text-rose-700' }}">
                {{ $m->type==='in'?'Entrada':'Salida' }}
              </span>
            </td>
            <td class="px-3 py-2">{{ $m->qty }}</td>
            <td class="px-3 py-2">Bs {{ number_format($m->unit_cost_at_issue,4) }}</td>
            <td class="px-3 py-2">Bs {{ number_format($m->qty * $m->unit_cost_at_issue,2) }}</td>
            <td class="px-3 py-2">{{ $m->notes ?: '—' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="mt-3">{{ $movs->links() }}</div>
  </div>
@endsection
