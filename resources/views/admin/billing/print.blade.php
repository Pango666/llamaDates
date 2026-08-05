<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo #{{ $invoice->number }}</title>
    @include('admin.pdf.partials.styles')
    <style>
        .identity-grid, .closing-grid { width: 100%; border-collapse: separate; border-spacing: 0; }
        .identity-grid td, .closing-grid td { border: 0; vertical-align: top; }
        .identity-grid td { padding: 3px 0 12px; }
        .closing-grid td { padding: 12px 0 0; }
        .info-label { color: #71869a; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
        .info-value { margin-top: 3px; color: #18364f; font-size: 12px; font-weight: 700; }
        .payment-line { padding: 5px 0; border-bottom: 1px dashed #dce7ee; color: #4c667c; font-size: 9px; }
        .thank-you { margin-top: 16px; padding: 10px; text-align: center; background: #f1fbfc; border: 1px solid #d7eff1; border-radius: 8px; color: #14717b; }
    </style>
</head>
<body>
    @php
        $calcSubtotal = $invoice->items->sum('total');
        $discount = (float) $invoice->discount;
        $taxPercent = (float) $invoice->tax_percent;
        $taxable = max(0, $calcSubtotal - $discount);
        $taxAmount = $taxable * ($taxPercent / 100);
        $finalTotal = $invoice->total > 0 ? $invoice->total : $taxable + $taxAmount;
        $paidAmount = $invoice->payments->sum('amount');
        $balance = $finalTotal - $paidAmount;
        $methods = ['cash'=>'Efectivo', 'card'=>'Tarjeta', 'transfer'=>'Transferencia', 'wallet'=>'Billetera'];
        $isPaid = $invoice->status === 'paid' || $balance <= 0.01;
    @endphp

    @include('admin.pdf.partials.footer', ['label' => 'Comprobante emitido por CEOT DATES'])

    @include('admin.pdf.partials.header', [
        'kicker' => 'Comprobante de pago',
        'title' => 'Recibo #'.$invoice->number,
        'subtitle' => $isPaid ? 'Pago completado' : 'Saldo pendiente',
    ])

    <table class="identity-grid">
        <tr>
            <td style="width:58%">
                <div class="info-label">Paciente</div>
                <div class="info-value">{{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}</div>
                @if($invoice->patient->ci)<div class="muted">CI: {{ $invoice->patient->ci }}</div>@endif
                @if($invoice->patient->email)<div class="muted">{{ $invoice->patient->email }}</div>@endif
            </td>
            <td style="width:42%; text-align:right">
                <span class="ceot-badge {{ $isPaid ? 'badge-green' : 'badge-amber' }}">{{ $isPaid ? 'Pagada' : 'Pendiente' }}</span>
                <div style="margin-top:9px"><span class="info-label">Fecha de emisión</span><br><span class="font-bold">{{ $invoice->issued_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}</span></div>
                <div style="margin-top:5px"><span class="info-label">Generado por</span><br>{{ auth()->user()->name ?? 'Administración' }}</div>
            </td>
        </tr>
    </table>

    <div class="ceot-section">Detalle de servicios</div>
    <table class="ceot-table">
        <thead><tr><th>Descripción</th><th class="text-center" style="width:12%">Cant.</th><th class="text-right" style="width:18%">Precio unit.</th><th class="text-right" style="width:18%">Total</th></tr></thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td><span class="font-bold">{{ $item->description ?: ($item->service->name ?? 'Servicio odontológico') }}</span>@if($item->details)<br><span class="small muted">{{ $item->details }}</span>@endif</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">Bs {{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right font-bold">Bs {{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="closing-grid">
        <tr>
            <td style="width:54%; padding-right:18px">
                @if($invoice->payments->count())
                    <div class="ceot-section" style="margin-top:0">Pagos registrados</div>
                    @foreach($invoice->payments as $payment)
                        <div class="payment-line">
                            {{ $payment->created_at->format('d/m/Y') }} | {{ $methods[$payment->method] ?? ucfirst($payment->method) }}
                            <span style="float:right; color:#08785c; font-weight:700">Bs {{ number_format($payment->amount, 2) }}</span>
                        </div>
                    @endforeach
                @endif
                @if($invoice->notes)
                    <div class="ceot-section">Observaciones</div>
                    <div class="ceot-note">{{ $invoice->notes }}</div>
                @endif
            </td>
            <td style="width:46%">
                <div class="ceot-total-card">
                    <table class="ceot-total-table">
                        <tr><td class="muted">Subtotal</td><td class="text-right">Bs {{ number_format($calcSubtotal, 2) }}</td></tr>
                        @if($discount > 0)<tr><td style="color:#a72a42">Descuento</td><td class="text-right" style="color:#a72a42">- Bs {{ number_format($discount, 2) }}</td></tr>@endif
                        @if($taxPercent > 0)<tr><td class="muted">Impuesto ({{ $taxPercent }}%)</td><td class="text-right">Bs {{ number_format($taxAmount, 2) }}</td></tr>@endif
                        <tr class="ceot-total-row"><td>Total</td><td class="text-right">Bs {{ number_format($finalTotal, 2) }}</td></tr>
                        <tr><td style="color:#08785c">Pagado</td><td class="text-right" style="color:#08785c">Bs {{ number_format($paidAmount, 2) }}</td></tr>
                        <tr><td style="color:{{ $balance > 0.01 ? '#956000' : '#536578' }}">Saldo</td><td class="text-right font-bold" style="color:{{ $balance > 0.01 ? '#956000' : '#536578' }}">Bs {{ number_format(max(0, $balance), 2) }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="thank-you">Gracias por confiar su sonrisa a nuestro equipo.</div>
</body>
</html>
