@extends('layouts.app')
@section('title','Cobrar plan')

@section('header-actions')
  <a href="{{ route('admin.plans.edit',$plan) }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver al Plan
  </a>
@endsection

@section('content')
  <div class="card">
    <h3 class="font-semibold mb-2">Cobrar por plan: {{ $plan->title }}</h3>
    <p class="text-sm text-slate-600 mb-4">
      Paciente: <span class="font-medium">{{ $plan->patient?->last_name }}, {{ $plan->patient?->first_name }}</span>
    </p>

    <div class="overflow-x-auto mb-4">
      <table class="min-w-full text-sm">
        <thead class="border-b">
          <tr class="text-left">
            <th class="px-3 py-2">Tratamiento</th>
            <th class="px-3 py-2 text-right">Precio</th>
          </tr>
        </thead>
        <tbody>
          @foreach($items as $it)
            <tr class="border-b">
              <td class="px-3 py-2">
                {{ $it->service?->name ?? 'Servicio' }}
                @if($it->tooth_code) · Pieza {{ $it->tooth_code }} {{ $it->surface ? ' '.$it->surface : '' }} @endif
              </td>
              <td class="px-3 py-2 text-right">Bs {{ number_format($it->price,2) }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot class="bg-slate-50">
          <tr>
            <td class="px-3 py-2 text-right">Subtotal</td>
            <td class="px-3 py-2 text-right font-medium">Bs {{ number_format($subtotal,2) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>

    <form method="post" action="{{ route('admin.plans.invoice.store',$plan) }}" class="grid md:grid-cols-6 gap-3">
      @csrf

      <div>
        <label class="block text-xs text-slate-500 mb-1">Descuento (Bs)</label>
        <input type="number" name="discount" step="0.01" min="0" value="{{ old('discount', $discount) }}" class="w-full border rounded px-2 py-2">
      </div>

      <div>
        <label class="block text-xs text-slate-500 mb-1">% Impuesto</label>
        <input type="number" name="tax_percent" step="0.01" min="0" max="100" value="{{ old('tax_percent', $taxPct) }}" class="w-full border rounded px-2 py-2">
      </div>

      <div class="md:col-span-2">
        <label class="block text-xs text-slate-500 mb-1">Notas</label>
        <input type="text" name="notes" value="{{ old('notes','?') }}" class="w-full border rounded px-2 py-2">
      </div>

      <div class="md:col-span-6 border-t pt-3"></div>

      <div>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="pay_now" value="1" @checked(old('pay_now'))> Registrar pago ahora
        </label>
      </div>

      <div>
        <label class="block text-xs text-slate-500 mb-1">Monto a pagar (Bs)</label>
        <input type="number" name="amount" step="0.01" min="0" value="{{ old('amount', $grand) }}" class="w-full border rounded px-2 py-2">
      </div>

      <div>
        <label class="block text-xs text-slate-500 mb-1">Método</label>
        <select name="method" class="w-full border rounded px-2 py-2">
          <option value="">—</option>
          <option value="cash"     @selected(old('method')==='cash')>Efectivo</option>
          <option value="card"     @selected(old('method')==='card')>Tarjeta</option>
          <option value="transfer" @selected(old('method')==='transfer')>Transferencia</option>
          <option value="wallet"   @selected(old('method')==='wallet')>Billetera</option>
        </select>
      </div>

      <div class="md:col-span-2 flex items-end justify-end">
        <button class="btn btn-primary">Generar Recibo</button>
      </div>
    </form>
  </div>
@endsection
