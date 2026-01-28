@extends('layouts.app')
@section('title', 'Recibo #' . $invoice->number)

@section('header-actions')
  <a href="{{ route('admin.billing') }}" class="btn bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors shadow-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver a Recibos
  </a>
@endsection

@section('content')
  @php
    $statusColors = match($invoice->status) {
      'paid' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
      'canceled' => 'bg-slate-100 text-slate-600 border-slate-200 line-through',
      default => 'bg-amber-50 text-amber-700 border-amber-200'
    };
    $statusLabel = match($invoice->status) {
        'draft' => 'Borrador',
        'issued' => 'Pendiente',
        'paid' => 'Pagada',
        'canceled' => 'Anulada',
        default => ucfirst($invoice->status)
    };
    $isLocked = in_array($invoice->status, ['paid', 'canceled']);
  @endphp

  <div class="max-w-7xl mx-auto space-y-6">

    {{-- Top Banner / Header --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-bold text-slate-800">Recibo #{{ $invoice->number }}</h1>
                <span class="px-3 py-1 rounded-full text-sm font-semibold border {{ $statusColors }}">
                    {{ $statusLabel }}
                </span>
            </div>
            <p class="text-slate-500 text-sm flex items-center gap-4">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Emitido: {{ $invoice->issued_at?->format('d M, Y') ?? '—' }}
                </span>
                @if($invoice->paid_at)
                    <span class="flex items-center gap-1 text-emerald-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Saldado el {{ $invoice->paid_at->format('d M, Y') }}
                    </span>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if($pdfExists)
                <a href="{{ route('admin.invoices.download', $invoice) }}?t={{ time() }}" target="_blank" class="btn bg-white border border-slate-300 text-slate-700 hover:text-blue-600 hover:border-blue-400 flex items-center gap-2 shadow-sm transition-all">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Ver Comprobante</span>
                </a>
            @endif

            <form action="{{ route('admin.invoices.regenerate', $invoice) }}" method="post">
                @csrf
                <button class="btn bg-blue-50 text-blue-600 hover:bg-blue-100 border border-transparent hover:border-blue-200 flex items-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    {{ $pdfExists ? 'Regenerar' : 'Generar PDF' }}
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left Column: Details --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Patient Info Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 text-blue-600 font-bold text-lg">
                        {{ substr($invoice->patient->first_name, 0, 1) }}{{ substr($invoice->patient->last_name, 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-slate-800 text-lg">
                            {{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-1 gap-x-4 mt-2 text-sm text-slate-600">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884-.895 1.6-2 1.6-1.105 0-2-.716-2-1.6V6m-2 2h12"/></svg>
                                CI: {{ $invoice->patient->ci ?? '—' }}
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $invoice->patient->phone ?? '—' }}
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ $invoice->patient->email ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items List --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-semibold text-slate-700">Detalle de Servicios</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">Descripción</th>
                            <th class="px-6 py-3 text-center font-medium">Cant.</th>
                            <th class="px-6 py-3 text-right font-medium">Precio</th>
                            <th class="px-6 py-3 text-right font-medium">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($invoice->items as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800">{{ $item->description }}</div>
                                @if($item->details || $item->service_id)
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        {{ $item->details ?? ($item->service?->name ?? 'Servicio Estándar') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-slate-600">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-right text-slate-600">Bs {{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-800">Bs {{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($invoice->notes)
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                    <p class="text-sm text-slate-600"><span class="font-semibold text-slate-700">Notas:</span> {{ $invoice->notes }}</p>
                </div>
                @endif
            </div>

        </div>

        {{-- Right Column: Summary & Actions --}}
        <div class="space-y-6">

            {{-- Totals Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-700 mb-4 pb-2 border-b border-slate-100">Resumen Económico</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>Subtotal</span>
                        <span>Bs {{ number_format($subtotal, 2) }}</span>
                    </div>
                    @if($invoice->discount > 0)
                    <div class="flex justify-between text-sm text-rose-600">
                        <span>Descuento</span>
                        <span>- Bs {{ number_format($invoice->discount, 2) }}</span>
                    </div>
                    @endif
                    @if($invoice->tax_percent > 0)
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>Impuesto ({{ $invoice->tax_percent }}%)</span>
                        <span>Bs {{ number_format($tax, 2) }}</span>
                    </div>
                    @endif
                    
                    <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
                        <span class="font-bold text-slate-800">Total Recibo</span>
                        <span class="font-bold text-xl text-slate-900">Bs {{ number_format($grand, 2) }}</span>
                    </div>

                    <div class="pt-3 flex justify-between items-center text-sm">
                        <span class="text-emerald-700 font-medium">Pagado</span>
                        <span class="font-bold text-emerald-700">Bs {{ number_format($paid, 2) }}</span>
                    </div>

                    <div class="flex justify-between items-center bg-slate-50 p-2 rounded border border-slate-100 mt-2">
                        <span class="font-semibold {{ $balance > 0 ? 'text-amber-700' : 'text-slate-500' }}">Saldo Pendiente</span>
                        <span class="font-bold {{ $balance > 0 ? 'text-amber-700' : 'text-slate-500' }}">Bs {{ number_format($balance, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment Form (If pending) --}}
            @unless($isLocked)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Registrar Cobro
                </h3>
                
                <form action="{{ route('admin.invoices.payments.store', $invoice) }}" method="post" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Monto a cobrar</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">Bs</span>
                            <input type="number" name="amount" step="0.01" min="0.01" max="{{ $balance }}" 
                                   value="{{ $balance }}" class="w-full pl-8 border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 font-semibold text-slate-800">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Método</label>
                            <select name="method" class="w-full border-slate-300 rounded-lg text-sm px-2 py-2">
                                <option value="cash">Efectivo</option>
                                <option value="card">Tarjeta</option>
                                <option value="transfer">Transferencia</option>
                                <option value="wallet">Billetera</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Referencia</label>
                            <input type="text" name="reference" placeholder="Opcional" class="w-full border-slate-300 rounded-lg text-sm px-2 py-2">
                        </div>
                    </div>

                    <button type="submit" class="w-full btn bg-blue-600 hover:bg-blue-700 text-white shadow-md">
                        Cobrar Monto
                    </button>
                </form>

                {{-- Close Invoice Action --}}
                @if($balance <= 0)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <form action="{{ route('admin.invoices.markPaid', $invoice) }}" method="post">
                            @csrf
                            <button class="w-full btn bg-emerald-600 hover:bg-emerald-700 text-white shadow-md flex justify-center items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Finalizar y Cerrar Recibo
                            </button>
                        </form>
                        <p class="text-xs text-slate-500 text-center mt-2">El saldo es 0. Puedes cerrar el recibo para marcarlo como PAGADO.</p>
                    </div>
                @else
                    <div class="mt-4 p-3 bg-amber-50 rounded text-xs text-amber-800 text-center border border-amber-100">
                        Debes cubrir el saldo total para cerrar el recibo.
                    </div>
                @endif
            </div>
            @endunless
            
            {{-- Payment History Timeline --}}
            @if($invoice->payments->count())
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-700 mb-4">Historial de Pagos</h3>
                <div class="relative border-l-2 border-slate-100 ml-2 space-y-6">
                    @foreach($invoice->payments as $payment)
                    <div class="ml-4">
                        <div class="absolute -left-1.5 mt-1.5 w-3 h-3 bg-emerald-400 rounded-full border border-white"></div>
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-emerald-700">+ Bs {{ number_format($payment->amount, 2) }}</h4>
                            <span class="text-xs text-slate-400">{{ $payment->created_at->format('d M, H:i') }}</span>
                        </div>
                        <p class="text-xs text-slate-600 capitalize">
                            {{ [
                                'cash' => 'Efectivo', 
                                'card' => 'Tarjeta', 
                                'transfer' => 'Transferencia', 
                                'wallet' => 'Billetera Digital'
                                ][$payment->method] ?? $payment->method 
                            }}
                            @if($payment->reference)
                             - <span class="text-slate-500">Ref: {{ $payment->reference }}</span>
                            @endif
                        </p>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between items-center text-sm font-medium">
                    <span class="text-slate-600">Total Recaudado:</span>
                    <span class="text-emerald-700">Bs {{ number_format($paid, 2) }}</span>
                </div>
            </div>
            @endif

        </div>
    </div>
  </div>

  @if(session('open_pdf') && $invoice->status === 'paid' && $pdfExists)
    <script>
      window.open("{{ route('admin.invoices.download', $invoice) }}?t={{ time() }}", "_blank");
    </script>
  @endif
@endsection
