@extends('layouts.app')
@section('title','Factura #'.$invoice->number)

@section('header-actions')
  @if($invoice->status==='paid')
    @if($pdfExists)
      <a href="{{ route('admin.invoices.download',$invoice) }}" class="btn btn-ghost" target="_blank" rel="noopener">
        Ver comprobante
      </a>
    @endif
    <form action="{{ route('admin.invoices.regenerate',$invoice) }}" method="post" class="inline">
      @csrf
      <button class="btn btn-ghost">{{ $pdfExists ? 'Regenerar PDF' : 'Generar PDF' }}</button>
    </form>
  @endif
@endsection

@section('content')
  @php
    $badge = match($invoice->status) {
      'paid'     => 'bg-emerald-100 text-emerald-700',
      'canceled' => 'bg-slate-200 text-slate-700 line-through',
      default    => 'bg-amber-100 text-amber-700'
    };
    $isLocked = in_array($invoice->status, ['paid','canceled']);
  @endphp

  <div class="grid gap-4 md:grid-cols-3">
    <section class="card md:col-span-2">
      <div class="flex items-center justify-between mb-2">
        <h3 class="font-semibold">Factura #{{ $invoice->number }}</h3>
        <span class="badge {{ $badge }}">{{ [
          'draft'=>'Borrador','issued'=>'Emitida','paid'=>'Pagada','canceled'=>'Anulada'
        ][$invoice->status] ?? $invoice->status }}</span>
      </div>
      <div class="text-sm text-slate-600 mb-3">
        Paciente:
        <span class="font-medium">{{ $invoice->patient->last_name }}, {{ $invoice->patient->first_name }}</span>
        · Emitida: {{ $invoice->issued_at?->format('Y-m-d H:i') ?? '—' }}
        @if($invoice->paid_at) · Pagada: {{ $invoice->paid_at->format('Y-m-d H:i') }} @endif
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="border-b">
            <tr class="text-left">
              <th class="px-3 py-2">Descripción</th>
              <th class="px-3 py-2">Cant.</th>
              <th class="px-3 py-2">P. unitario</th>
              <th class="px-3 py-2">Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach($invoice->items as $it)
              <tr class="border-b">
                <td class="px-3 py-2">{{ $it->description }}</td>
                <td class="px-3 py-2">{{ $it->quantity }}</td>
                <td class="px-3 py-2">Bs {{ number_format($it->unit_price,2) }}</td>
                <td class="px-3 py-2">Bs {{ number_format($it->total,2) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>

    <aside class="card">
      <h4 class="font-semibold mb-2">Totales</h4>
      <dl class="text-sm space-y-1">
        <div class="flex justify-between"><dt>Subtotal</dt><dd>Bs {{ number_format($subtotal,2) }}</dd></div>
        <div class="flex justify-between"><dt>Descuento</dt><dd>- Bs {{ number_format($invoice->discount,2) }}</dd></div>
        <div class="flex justify-between"><dt>Impuesto ({{ $invoice->tax_percent }}%)</dt><dd>Bs {{ number_format($tax,2) }}</dd></div>
        <div class="flex justify-between font-medium"><dt>Total</dt><dd>Bs {{ number_format($grand,2) }}</dd></div>
        <div class="flex justify-between"><dt>Pagado</dt><dd>Bs {{ number_format($paid,2) }}</dd></div>
        <div class="flex justify-between font-semibold"><dt>Saldo</dt><dd>Bs {{ number_format($balance,2) }}</dd></div>
      </dl>

      {{-- Pago / Acciones: bloqueado si pagada o anulada --}}
      @unless($isLocked)
        <form action="{{ route('admin.invoices.payments.store',$invoice) }}" method="post" class="mt-3">
          @csrf
          <label class="block text-xs text-slate-500 mb-1">Registrar pago</label>
          <div class="grid grid-cols-2 gap-2">
            <input type="number" name="amount" step="0.01" min="0.01"
                   class="border rounded px-2 py-2" placeholder="Monto"
                   value="{{ old('amount', $balance) }}">
            <select name="method" class="border rounded px-2 py-2">
              <option value="cash">Efectivo</option>
              <option value="card">Tarjeta</option>
              <option value="transfer">Transferencia</option>
              <option value="wallet">Billetera</option>
            </select>
            <input type="text" name="reference" class="border rounded px-2 py-2 col-span-2"
                   placeholder="Referencia (voucher/tx)">
          </div>
          <button class="btn btn-primary mt-2 w-full">Agregar pago</button>
        </form>

        <div class="mt-3">
          <form action="{{ route('admin.invoices.markPaid',$invoice) }}" method="post">
            @csrf
            <button class="btn bg-emerald-600 text-white w-full" @disabled($balance>0)>
              Pagar y descargar PDF
            </button>
          </form>
          @if($balance>0)
            <p class="text-xs text-slate-500 mt-1">
              Ingresa pagos hasta cubrir el saldo para poder marcarla como pagada.
            </p>
          @endif
        </div>
      @endunless

      @if($invoice->payments->count())
        <h4 class="font-semibold mt-4 mb-2">Pagos</h4>
        <ul class="text-sm space-y-1">
          @foreach($invoice->payments as $p)
            <li class="flex justify-between">
              <span>{{ ucfirst($p->method) }} {{ $p->reference ? '· '.$p->reference : '' }}</span>
              <span>Bs {{ number_format($p->amount,2) }}</span>
            </li>
          @endforeach
        </ul>
      @endif
    </aside>
  </div>

  {{-- Si venimos de "Pagar", abrir comprobante en una pestaña nueva --}}
  @if(session('open_pdf') && $invoice->status==='paid' && $pdfExists)
    <script>
      window.open("{{ route('admin.invoices.download',$invoice) }}", "_blank");
    </script>
  @endif
@endsection
