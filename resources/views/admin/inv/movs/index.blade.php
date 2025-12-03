@extends('layouts.app')
@section('title','Movimientos de Inventario')

@section('header-actions')
  <a href="{{ route('admin.inv.movs.create') }}" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Nuevo Movimiento
  </a>
@endsection

@section('content')
  <div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h8"/>
          </svg>
          Movimientos de Inventario
        </h1>
        <p class="text-sm text-slate-600 mt-1">
          Entradas, salidas y ajustes de medicamentos, insumos y materiales.
        </p>
      </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-6">
      <form method="get" class="grid gap-4 md:grid-cols-7 md:items-end">
        {{-- Producto --}}
        <div class="space-y-2 md:col-span-2">
          <label class="block text-sm font-medium text-slate-700">Producto</label>
          <select
            name="product_id"
            class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
            <option value="0">Todos</option>
            @foreach($products as $p)
              <option value="{{ $p->id }}" @selected($qProd == $p->id)>
                {{ $p->name }} @if($p->sku) ({{ $p->sku }}) @endif
              </option>
            @endforeach
          </select>
        </div>

        {{-- Ubicación --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700">Ubicación</label>
          <select
            name="location_id"
            class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
            <option value="0">Todas</option>
            @foreach($locations as $loc)
              <option value="{{ $loc->id }}" @selected($qLoc == $loc->id)>
                {{ $loc->name }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Usuario --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700">Usuario</label>
          <select
            name="user_id"
            class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
            <option value="0">Todos</option>
            @foreach($users as $u)
              <option value="{{ $u->id }}" @selected($qUser == $u->id)>
                {{ $u->name }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Tipo --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700">Tipo</label>
          <select
            name="type"
            class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
            <option value="all" @selected($type === 'all')>Todos</option>
            <option value="in"  @selected($type === 'in')>Entrada</option>
            <option value="out" @selected($type === 'out')>Salida</option>
          </select>
        </div>

        {{-- Desde --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700">Desde</label>
          <input
            type="date"
            name="from"
            value="{{ $from }}"
            class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
        </div>

        {{-- Hasta --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700">Hasta</label>
          <input
            type="date"
            name="to"
            value="{{ $to }}"
            class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
        </div>

        {{-- Botones --}}
        <div class="flex gap-2">
          <button class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
            Filtrar
          </button>
          @if($qProd || $qLoc || $qUser || $type !== 'all' || $from || $to)
            <a href="{{ route('admin.inv.movs.index') }}" class="btn btn-ghost">
              Limpiar
            </a>
          @endif
        </div>
      </form>
    </div>

    {{-- Tabla --}}
    <div class="card p-0 overflow-hidden">
      @if($movs->count())
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Fecha</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Producto</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Ubicación</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Registrado por</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Tipo</th>
                <th class="px-4 py-3 text-right font-semibold text-slate-700">Cantidad</th>
                <th class="px-4 py-3 text-right font-semibold text-slate-700">Precio compra</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Lote / Venc.</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Factura</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              @foreach($movs as $mov)
                <tr class="hover:bg-slate-50">
                  {{-- Fecha --}}
                  <td class="px-4 py-3 text-slate-600">
                    {{ $mov->created_at->format('d/m/Y H:i') }}
                  </td>

                  {{-- Producto --}}
                  <td class="px-4 py-3">
                    <div class="text-slate-800 font-medium">
                      {{ $mov->product->name ?? '—' }}
                    </div>
                    <div class="text-xs text-slate-500">
                      @if($mov->product?->sku)
                        SKU: {{ $mov->product->sku }}
                      @endif
                      @if($mov->product?->unit)
                        @if($mov->product?->sku) · @endif
                        Unidad: {{ $mov->product->unit }}
                      @endif
                    </div>
                  </td>

                  {{-- Ubicación --}}
                  <td class="px-4 py-3 text-slate-700">
                    {{ $mov->location->name ?? '—' }}
                  </td>

                  {{-- Usuario --}}
                  <td class="px-4 py-3 text-slate-700">
                    {{ $mov->user->name ?? '—' }}
                  </td>

                  {{-- Tipo --}}
                  <td class="px-4 py-3">
                    @php
                      $label = [
                        'in'       => 'Entrada',
                        'out'      => 'Salida',
                        'adjust'   => 'Ajuste',
                        'transfer' => 'Traslado',
                      ][$mov->type] ?? $mov->type;

                      $color = match($mov->type) {
                        'in'      => 'bg-emerald-100 text-emerald-800',
                        'out'     => 'bg-rose-100 text-rose-800',
                        'adjust'  => 'bg-amber-100 text-amber-800',
                        default   => 'bg-slate-100 text-slate-800',
                      };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                      {{ $label }}
                    </span>
                  </td>

                  {{-- Cantidad --}}
                  <td class="px-4 py-3 text-right">
                    {{ number_format($mov->qty, 2) }}
                    @if($mov->product?->unit)
                      <span class="text-xs text-slate-500">{{ $mov->product->unit }}</span>
                    @endif
                  </td>

                  {{-- Precio compra --}}
                  <td class="px-4 py-3 text-right text-slate-700">
                    @if($mov->unit_cost !== null)
                      Bs {{ number_format($mov->unit_cost, 2) }}
                    @else
                      —
                    @endif
                  </td>

                  {{-- Lote / Vencimiento --}}
                  <td class="px-4 py-3 text-slate-600">
                    @if($mov->lot)
                      <div>
                        Lote:
                        <span class="font-mono text-xs">{{ $mov->lot }}</span>
                      </div>
                    @endif
                    @if($mov->expires_at)
                      <div class="text-xs">
                        Vence: {{ \Carbon\Carbon::parse($mov->expires_at)->format('d/m/Y') }}
                      </div>
                    @endif
                    @if(!$mov->lot && !$mov->expires_at)
                      —
                    @endif
                  </td>

                  {{-- Factura --}}
                  <td class="px-4 py-3 text-slate-600">
                    {{ $mov->purchase_invoice_number ?? '—' }}
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="text-center py-10">
          <p class="text-slate-600">No hay movimientos registrados.</p>
        </div>
      @endif
    </div>

    @if($movs->hasPages())
      <div class="mt-4">
        {{ $movs->links() }}
      </div>
    @endif
  </div>
@endsection
