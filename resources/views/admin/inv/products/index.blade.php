@extends('layouts.app')
@section('title','Productos')

@section('header-actions')
  <a href="{{ route('admin.inv.products.create') }}" class="btn">+ Nuevo producto</a>
@endsection

@section('content')
  <div class="card mb-3">
    <form method="get" class="flex gap-2 items-end">
      <div>
        <label class="text-xs text-slate-500">Buscar</label>
        <input name="q" value="{{ $q }}" class="border rounded px-3 py-2" placeholder="Nombre o SKU">
      </div>
      <div>
        <label class="text-xs text-slate-500">Estado</label>
        <select name="active" class="border rounded px-3 py-2">
          <option value="all" @selected($active==='all')>Todos</option>
          <option value="1" @selected($active==='1')>Activos</option>
          <option value="0" @selected($active==='0')>Inactivos</option>
        </select>
      </div>
      <button class="btn">Filtrar</button>
    </form>
  </div>

  <div class="card overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="border-b">
        <tr class="text-left">
          <th class="px-3 py-2">SKU</th>
          <th class="px-3 py-2">Producto</th>
          <th class="px-3 py-2">Unidad</th>
          <th class="px-3 py-2">P. venta</th>
          <th class="px-3 py-2">Costo prom.</th>
          <th class="px-3 py-2">Stock</th>
          <th class="px-3 py-2">Estado</th>
          <th class="px-3 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach($products as $p)
          <tr class="border-b">
            <td class="px-3 py-2">{{ $p->sku ?: '—' }}</td>
            <td class="px-3 py-2">{{ $p->name }}</td>
            <td class="px-3 py-2">{{ $p->unit }}</td>
            <td class="px-3 py-2">Bs {{ number_format($p->sell_price,2) }}</td>
            <td class="px-3 py-2">Bs {{ number_format($p->cost_avg,2) }}</td>
            <td class="px-3 py-2">{{ (float)($stockMap[$p->id] ?? 0) }}</td>
            <td class="px-3 py-2">
              <span class="badge {{ $p->active?'bg-emerald-100 text-emerald-700':'bg-slate-200 text-slate-600' }}">
                {{ $p->active ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td class="px-3 py-2 text-right">
              <a class="btn btn-ghost" href="{{ route('admin.inv.products.edit',$p) }}">Editar</a>
              <form action="{{ route('admin.inv.products.destroy',$p) }}" method="post" class="inline"
                    onsubmit="return confirm('¿Eliminar producto?');">
                @csrf @method('DELETE')
                <button class="btn btn-danger">Eliminar</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="mt-3">{{ $products->links() }}</div>
  </div>
@endsection
