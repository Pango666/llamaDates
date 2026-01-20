@extends('patient.layout')
@section('title','Recibo #'.$invoice->number)

@section('content')
@php
  $issuedAt = $invoice->issued_at ?? $invoice->created_at;

  $statusLabel = $isPaid ? 'Pagada' : 'Pendiente';
  $statusClass = $isPaid
    ? 'bg-emerald-100 text-emerald-800 border border-emerald-200'
    : 'bg-amber-100 text-amber-800 border border-amber-200';
@endphp

<div class="grid gap-4">
  {{-- Header del recibo --}}
  <section class="card">
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
      <div>
        <h2 class="text-lg font-semibold text-slate-800">Recibo #{{ $invoice->number }}</h2>

        <div class="mt-1 text-sm text-slate-600">
          <div>Emitido: <span class="font-medium text-slate-800">{{ $issuedAt->format('d/m/Y H:i') }}</span></div>

          @if($invoice->appointment)
            <div class="mt-1">
              Cita:
              <span class="font-medium text-slate-800">
                {{ \Illuminate\Support\Carbon::parse($invoice->appointment->date)->format('d/m/Y') }}
                · {{ \Illuminate\Support\Str::substr($invoice->appointment->start_time,0,5) }}
              </span>
            </div>
          @endif
        </div>
      </div>

      <div class="flex items-center gap-2 md:justify-end">
        <span class="badge {{ $statusClass }} px-3 py-1 text-xs font-semibold">
          {{ $statusLabel }}
        </span>

        <a href="{{ route('app.invoices.index') }}" class="btn btn-ghost">
          Ver todos
        </a>
      </div>
    </div>
  </section>

  {{-- Detalle de items --}}
  <section class="card">
    <div class="flex items-center justify-between gap-2 mb-3">
      <h3 class="font-semibold text-slate-800">Detalle del recibo</h3>
      <div class="text-xs text-slate-500">
        Items: {{ $invoice->items->count() }}
      </div>
    </div>

    <div class="overflow-x-auto border rounded-lg">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 border-b">
          <tr class="text-left text-slate-600">
            <th class="px-4 py-3 font-semibold">Descripción</th>
            <th class="px-4 py-3 font-semibold text-right">Cant.</th>
            <th class="px-4 py-3 font-semibold text-right">Precio unit.</th>
            <th class="px-4 py-3 font-semibold text-right">Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach($invoice->items as $it)
            <tr class="border-b last:border-b-0">
              <td class="px-4 py-3">
                <div class="font-medium text-slate-800">{{ $it->description }}</div>
                @if($it->service)
                  <div class="text-xs text-slate-500 mt-0.5">{{ $it->service->name }}</div>
                @endif
              </td>
              <td class="px-4 py-3 text-right text-slate-700">{{ $it->quantity }}</td>
              <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float)$it->unit_price, 2) }}</td>
              <td class="px-4 py-3 text-right font-semibold text-slate-800">{{ number_format((float)$it->total, 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>

  {{-- Totales + Pagos --}}
  <section class="grid gap-4 lg:grid-cols-2">
    {{-- Totales --}}
    <div class="card">
      <h3 class="font-semibold text-slate-800 mb-3">Resumen</h3>

      <div class="space-y-2 text-sm">
        <div class="flex items-center justify-between">
          <span class="text-slate-600">Subtotal</span>
          <span class="font-medium text-slate-800">{{ number_format((float)$subtotal, 2) }}</span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-slate-600">Descuento</span>
          <span class="font-medium text-slate-800">- {{ number_format((float)$discount, 2) }}</span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-slate-600">Impuesto ({{ number_format((float)$taxPercent, 2) }}%)</span>
          <span class="font-medium text-slate-800">{{ number_format((float)$tax, 2) }}</span>
        </div>

        <div class="border-t pt-2 flex items-center justify-between">
          <span class="text-slate-700 font-semibold">Total</span>
          <span class="text-slate-900 font-bold">{{ number_format((float)$grand, 2) }}</span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-slate-600">Pagado</span>
          <span class="font-semibold text-emerald-700">{{ number_format((float)$paid, 2) }}</span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-slate-700 font-semibold">Saldo</span>
          <span class="font-bold {{ $balance > 0 ? 'text-rose-700' : 'text-emerald-700' }}">
            {{ number_format((float)$balance, 2) }}
          </span>
        </div>
      </div>

      <div class="mt-4 flex flex-wrap gap-2">
        <a href="{{ route('app.invoices.index') }}"
     class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium
            border border-slate-300 bg-white text-slate-800 hover:bg-slate-50 transition">
    Volver a recibos
  </a>
        <a href="{{ route('app.appointments.index') }}"
     class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium
            border border-slate-300 bg-white text-slate-800 hover:bg-slate-50 transition">
    Mis citas
  </a>
      </div>
    </div>

    {{-- Pagos --}}
    <div class="card">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold text-slate-800">Pagos</h3>
        @if(!$isPaid)
    <a href="{{ route('app.invoices.index') }}"
       class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold
              bg-blue-600 text-white hover:bg-blue-700 transition">
      Ir a pagos
    </a>
  @endif
      </div>

      @if($invoice->payments->count())
        <div class="overflow-x-auto border rounded-lg">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b">
              <tr class="text-left text-slate-600">
                <th class="px-4 py-3 font-semibold">Fecha</th>
                <th class="px-4 py-3 font-semibold">Método</th>
                <th class="px-4 py-3 font-semibold text-right">Monto</th>
              </tr>
            </thead>
            <tbody>
              @foreach($invoice->payments as $p)
                <tr class="border-b last:border-b-0">
                  <td class="px-4 py-3 text-slate-700">
                    {{ ($p->paid_at ?? $p->created_at)->format('d/m/Y H:i') }}
                  </td>
                  <td class="px-4 py-3 text-slate-700">
                    {{ $p->method ?? '—' }}
                  </td>
                  <td class="px-4 py-3 text-right font-semibold text-slate-800">
                    {{ number_format((float)($p->amount ?? 0), 2) }}
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="text-sm text-slate-600">
          Aún no registras pagos para este recibo.
        </div>

        @if(!$isPaid)
          <div class="mt-3 text-xs text-slate-500">
            Cuando realices un pago, aparecerá aquí automáticamente.
          </div>
        @endif
      @endif
    </div>
  </section>
</div>
@endsection
