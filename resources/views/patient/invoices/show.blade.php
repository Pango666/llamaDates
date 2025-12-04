@extends('layouts.app')
@section('title','Recibo #'.$invoice->number)

@section('content')
  <div class="grid gap-4">
    <section class="card">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="font-semibold">Recibo #{{ $invoice->number }}</h3>
          <div class="text-xs text-slate-500">Emitida: {{ $invoice->issued_at?->format('Y-m-d H:i') ?? $invoice->created_at->format('Y-m-d H:i') }}</div>
          @if($invoice->appointment)
            <div class="text-xs text-slate-500">Cita: {{ $invoice->appointment->date }} · {{ \Illuminate\Support\Str::substr($invoice->appointment->start_time,0,5) }}</div>
          @endif
        </div>
        <span class="badge {{ $invoice->status==='paid' ? 'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700' }}">
          {{ $invoice->status==='paid' ? 'Pagada' : 'Pendiente' }}
        </span>
      </div>
    </section>

    <section class="card">
      <h3 class="font-semibold mb-2">Detalles</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="border-b">
            <tr class="text-left">
              <th class="px-3 py-2">Descripción</th>
              <th class="px-3 py-2 text-right">Cant</th>
              <th class="px-3 py-2 text-right">Unit</th>
              <th class="px-3 py-2 text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach($invoice->items as $it)
              <tr class="border-b">
                <td class="px-3 py-2">{{ $it->description }}</td>
                <td class="px-3 py-2 text-right">{{ $it->quantity }}</td>
                <td class="px-3 py-2 text-right">{{ number_format($it->unit_price,2) }}</td>
                <td class="px-3 py-2 text-right">{{ number_format($it->total,2) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>

    {{-- <section class="card">
      <h3 class="font-semibold mb-2">Totales</h3>
      <div class="text-sm">
        <div>Subtotal: <b>{{ number_format($subtotal,2) }}</b></div>
        <div>Descuento: <b>{{ number_format($invoice->discount,2) }}</b></div>
        <div>Impuesto ({{ number_format($invoice->tax_percent,2) }}%): <b>{{ number_format($tax,2) }}</b></div>
        <div>Total: <b>{{ number_format($grand,2) }}</b></div>
        <div>Pagado: <b>{{ number_format($paid,2) }}</b></div>
        <div>Saldo: <b>{{ number_format($balance,2) }}</b></div>
      </div>
    </section> --}}
  </div>
@endsection
