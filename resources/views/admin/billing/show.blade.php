@extends('layouts.app')
@section('title', 'Factura #' . $invoice->number)

@section('header-actions')
  <a href="{{ route('admin.billing') }}" class="btn bg-slate-600 text-white hover:bg-slate-700 flex items-center gap-2 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Facturas
  </a>
  
  @if($invoice->status === 'paid')
    @if($pdfExists)
      <a href="{{ route('admin.invoices.download', $invoice) }}" class="btn bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2" target="_blank" rel="noopener">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Ver Comprobante
      </a>
    @endif
    <form action="{{ route('admin.invoices.regenerate', $invoice) }}" method="post" class="inline">
      @csrf
      <button class="btn bg-amber-600 text-white hover:bg-amber-700 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        {{ $pdfExists ? 'Regenerar PDF' : 'Generar PDF' }}
      </button>
    </form>
  @endif
@endsection

@section('content')
  @php
    $statusConfig = match($invoice->status) {
      'paid' => ['class' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'icon' => 'check'],
      'canceled' => ['class' => 'bg-slate-200 text-slate-700 border-slate-300 line-through', 'icon' => 'x'],
      default => ['class' => 'bg-amber-100 text-amber-700 border-amber-200', 'icon' => 'clock']
    };
    $isLocked = in_array($invoice->status, ['paid', 'canceled']);
  @endphp

  <div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="card mb-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Factura #{{ $invoice->number }}
          </h1>
          <div class="flex flex-wrap items-center gap-4 mt-2 text-sm text-slate-600">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <span class="font-medium">{{ $invoice->patient->last_name }}, {{ $invoice->patient->first_name }}</span>
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              Emitida: {{ $invoice->issued_at?->format('d/m/Y H:i') ?? '—' }}
            </div>
            @if($invoice->paid_at)
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Pagada: {{ $invoice->paid_at->format('d/m/Y H:i') }}
              </div>
            @endif
          </div>
        </div>
        
        <div class="flex items-center gap-3">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border {{ $statusConfig['class'] }}">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              @if($statusConfig['icon'] === 'check')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              @elseif($statusConfig['icon'] === 'x')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              @else
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              @endif
            </svg>
            {{ ['draft' => 'Borrador', 'issued' => 'Emitida', 'paid' => 'Pagada', 'canceled' => 'Anulada'][$invoice->status] ?? $invoice->status }}
          </span>
        </div>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
      {{-- Detalles de la Factura --}}
      <section class="lg:col-span-2">
        <div class="card">
          <div class="border-b border-slate-200 pb-4 mb-4">
            <h3 class="font-semibold text-slate-800 flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
              Detalles de la Factura
            </h3>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                  <th class="px-4 py-3 font-semibold text-slate-700">Descripción</th>
                  <th class="px-4 py-3 font-semibold text-slate-700 text-center">Cantidad</th>
                  <th class="px-4 py-3 font-semibold text-slate-700 text-right">Precio Unitario</th>
                  <th class="px-4 py-3 font-semibold text-slate-700 text-right">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200">
                @foreach($invoice->items as $item)
                  <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3">
                      <div class="font-medium text-slate-800">{{ $item->description }}</div>
                      @if($item->details)
                        <div class="text-xs text-slate-500 mt-1">{{ $item->details }}</div>
                      @endif
                    </td>
                    <td class="px-4 py-3 text-center text-slate-600">
                      {{ $item->quantity }}
                    </td>
                    <td class="px-4 py-3 text-right font-medium text-slate-800">
                      Bs {{ number_format($item->unit_price, 2) }}
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-blue-600">
                      Bs {{ number_format($item->total, 2) }}
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </section>

      {{-- Panel Lateral --}}
      <aside class="space-y-6">
        {{-- Resumen de Totales --}}
        <div class="card">
          <h4 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            Resumen de Totales
          </h4>
          
          <dl class="space-y-3">
            <div class="flex justify-between items-center">
              <dt class="text-sm text-slate-600">Subtotal</dt>
              <dd class="font-medium text-slate-800">Bs {{ number_format($subtotal, 2) }}</dd>
            </div>
            
            <div class="flex justify-between items-center">
              <dt class="text-sm text-slate-600">Descuento</dt>
              <dd class="font-medium text-rose-600">- Bs {{ number_format($invoice->discount, 2) }}</dd>
            </div>
            
            <div class="flex justify-between items-center">
              <dt class="text-sm text-slate-600">Impuesto ({{ $invoice->tax_percent }}%)</dt>
              <dd class="font-medium text-slate-800">Bs {{ number_format($tax, 2) }}</dd>
            </div>
            
            <div class="border-t border-slate-200 pt-3 flex justify-between items-center">
              <dt class="font-semibold text-slate-800">Total</dt>
              <dd class="font-bold text-lg text-blue-600">Bs {{ number_format($grand, 2) }}</dd>
            </div>
            
            <div class="flex justify-between items-center">
              <dt class="text-sm text-slate-600">Pagado</dt>
              <dd class="font-medium text-emerald-600">Bs {{ number_format($paid, 2) }}</dd>
            </div>
            
            <div class="border-t border-slate-200 pt-3 flex justify-between items-center">
              <dt class="font-semibold {{ $balance > 0 ? 'text-amber-600' : 'text-slate-800' }}">Saldo</dt>
              <dd class="font-bold text-lg {{ $balance > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                Bs {{ number_format($balance, 2) }}
              </dd>
            </div>
          </dl>
        </div>

        {{-- Gestión de Pagos --}}
        @unless($isLocked)
          <div class="card">
            <h4 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
              </svg>
              Registrar Pago
            </h4>

            <form action="{{ route('admin.invoices.payments.store', $invoice) }}" method="post" class="space-y-3">
              @csrf
              
              <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-700">Monto</label>
                <input 
                  type="number" 
                  name="amount" 
                  step="0.01" 
                  min="0.01" 
                  max="{{ $balance }}"
                  value="{{ old('amount', $balance) }}"
                  class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                  placeholder="0.00"
                  required
                >
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-700">Método de Pago</label>
                <select 
                  name="method" 
                  class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                >
                  <option value="cash">💵 Efectivo</option>
                  <option value="card">💳 Tarjeta</option>
                  <option value="transfer">🏦 Transferencia</option>
                  <option value="wallet">📱 Billetera Digital</option>
                </select>
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-700">Referencia</label>
                <input 
                  type="text" 
                  name="reference" 
                  class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                  placeholder="Número de voucher o transacción"
                >
              </div>

              <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 w-full flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Agregar Pago
              </button>
            </form>

            {{-- Marcar como Pagada --}}
            <div class="mt-4 pt-4 border-t border-slate-200">
              <form action="{{ route('admin.invoices.markPaid', $invoice) }}" method="post">
                @csrf
                <button 
                  class="btn bg-emerald-600 text-white hover:bg-emerald-700 w-full flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                  @disabled($balance > 0)
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Pagar y Descargar PDF
                </button>
              </form>
              
              @if($balance > 0)
                <p class="text-xs text-amber-600 mt-2 text-center">
                  💡 Ingresa pagos hasta cubrir el saldo para poder marcarla como pagada.
                </p>
              @endif
            </div>
          </div>
        @endunless

        {{-- Historial de Pagos --}}
        @if($invoice->payments->count())
          <div class="card">
            <h4 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Historial de Pagos
            </h4>
            
            <div class="space-y-3">
              @foreach($invoice->payments as $payment)
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                      @switch($payment->method)
                        @case('cash')
                          <span class="text-sm">💵</span>
                          @break
                        @case('card')
                          <span class="text-sm">💳</span>
                          @break
                        @case('transfer')
                          <span class="text-sm">🏦</span>
                          @break
                        @case('wallet')
                          <span class="text-sm">📱</span>
                          @break
                      @endswitch
                    </div>
                    <div>
                      <div class="font-medium text-slate-800 capitalize">{{ $payment->method }}</div>
                      @if($payment->reference)
                        <div class="text-xs text-slate-500">{{ $payment->reference }}</div>
                      @endif
                      <div class="text-xs text-slate-500">{{ $payment->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                  </div>
                  <div class="font-bold text-emerald-600">
                    Bs {{ number_format($payment->amount, 2) }}
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endif
      </aside>
    </div>
  </div>

  {{-- Si venimos de "Pagar", abrir comprobante en una pestaña nueva --}}
  @if(session('open_pdf') && $invoice->status === 'paid' && $pdfExists)
    <script>
      window.open("{{ route('admin.invoices.download', $invoice) }}", "_blank");
    </script>
  @endif
@endsection