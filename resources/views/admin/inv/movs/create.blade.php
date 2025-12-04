@extends('layouts.app')
@section('title','Nuevo Movimiento de Inventario')

@section('header-actions')
  <a href="{{ route('admin.inv.movs.index') }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Movimientos
  </a>
@endsection

@section('content')
  <div class="max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="card mb-6">
      <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nuevo Movimiento
        </h1>
        <p class="text-sm text-slate-600 mt-1">
          Registre una entrada o salida de inventario. Las entradas pueden registrar precio de compra y número de recibo.
        </p>
      </div>
    </div>

    <form method="post" action="{{ route('admin.inv.movs.store') }}" class="card">
      @csrf

      {{-- Producto y ubicación --}}
      <div class="grid gap-6 md:grid-cols-2 mb-6">
        {{-- Producto --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700">
            Producto <span class="text-red-500">*</span>
          </label>
          <select
            name="product_id"
            required
            class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
            <option value="">Seleccione...</option>
            @foreach($products as $p)
              <option value="{{ $p->id }}" @selected(old('product_id', $movement->product_id ?? null) == $p->id)>
                {{ $p->name }} @if($p->sku) ({{ $p->sku }}) @endif
              </option>
            @endforeach
          </select>
        </div>

        {{-- Ubicación --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700">
            Ubicación <span class="text-red-500">*</span>
          </label>
          <select
            name="location_id"
            required
            class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
            <option value="">Seleccione...</option>
            @foreach($locations as $loc)
              <option value="{{ $loc->id }}" @selected(old('location_id', $movement->location_id ?? null) == $loc->id)>
                {{ $loc->name }} @if($loc->is_main) (Principal) @endif
              </option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- Tipo, cantidad, costo --}}
      <div class="grid gap-6 md:grid-cols-3 mb-6">
        {{-- Tipo --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700">
            Tipo <span class="text-red-500">*</span>
          </label>
          <select
            name="type"
            required
            class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
            @php($typeOld = old('type', $movement->type ?? 'in'))
            <option value="in"  @selected($typeOld === 'in')>Entrada</option>
            <option value="out" @selected($typeOld === 'out')>Salida</option>
          </select>
        </div>

        {{-- Cantidad --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700">
            Cantidad <span class="text-red-500">*</span>
          </label>
          <input
            type="number"
            name="qty"
            step="0.001"
            min="0.0001"
            value="{{ old('qty', $movement->qty ?? 1) }}"
            required
            class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
        </div>

        {{-- Precio compra --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700">
            Precio de compra (Bs)
          </label>
          <input
            type="number"
            name="unit_cost"
            step="0.0001"
            min="0"
            value="{{ old('unit_cost', $movement->unit_cost ?? '') }}"
            class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            placeholder="Obligatorio en entradas"
          >
        </div>
      </div>

      {{-- Lote, vencimiento, factura --}}
      <div class="grid gap-6 md:grid-cols-3 mb-6">
        {{-- Lote --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700">
            Lote
          </label>
          <input
            type="text"
            name="lot"
            value="{{ old('lot', $movement->lot ?? '') }}"
            class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            placeholder="Código de lote"
          >
        </div>

        {{-- Vencimiento --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700">
            Fecha de vencimiento
          </label>
          <input
            type="date"
            name="expires_at"
            value="{{ old('expires_at', isset($movement->expires_at) ? \Carbon\Carbon::parse($movement->expires_at)->format('Y-m-d') : '') }}"
            class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          >
        </div>

        {{-- N° factura --}}
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700">
            N° recibo / comprobante
          </label>
          <input
            type="text"
            name="purchase_invoice_number"
            value="{{ old('purchase_invoice_number', $movement->purchase_invoice_number ?? '') }}"
            class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            placeholder="Recibo de compra asociada"
          >
        </div>
      </div>

      {{-- Nota --}}
      <div class="mb-6 space-y-2">
        <label class="block text-sm font-medium text-slate-700">
          Nota / observaciones
        </label>
        <textarea
          name="note"
          rows="3"
          class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
          placeholder="Motivo del movimiento, referencia interna, etc."
        >{{ old('note', $movement->note ?? '') }}</textarea>
      </div>

      {{-- Acciones --}}
      <div class="flex items-center gap-4 pt-6 border-t border-slate-200">
        <button class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2">
          Guardar Movimiento
        </button>
        <a href="{{ route('admin.inv.movs.index') }}" class="btn btn-ghost">
          Cancelar
        </a>
      </div>
    </form>
  </div>
@endsection
