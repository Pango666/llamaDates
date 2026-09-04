<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Cobradores</title>
    @include('admin.pdf.partials.styles')
    <style>
        .collector-header {
            margin: 16px 0 6px;
            padding: 8px 12px;
            background: #eef3ff;
            border-left: 4px solid #4f6df5;
            border-radius: 0 6px 6px 0;
        }
        .collector-name {
            font-size: 13px;
            font-weight: 700;
            color: #1e3a5f;
        }
        .collector-subtotal {
            float: right;
            font-size: 11px;
            font-weight: 700;
            color: #08785c;
        }
        .date-row {
            background: #f7fafc;
            padding: 4px 12px;
            font-size: 9px;
            font-weight: 700;
            color: #526a80;
            border-bottom: 1px solid #e2e8f0;
        }
        .method-badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 8px;
            font-size: 8px;
            font-weight: 600;
        }
        .method-cash      { background: #dcfce7; color: #166534; }
        .method-card      { background: #f3e8ff; color: #6b21a8; }
        .method-transfer  { background: #e0f2fe; color: #075985; }
        .method-wallet    { background: #ffedd5; color: #9a3412; }

        .grand-total {
            margin-top: 14px;
            padding: 10px 14px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
        }
        .grand-total-label { font-size: 11px; font-weight: 700; color: #14532d; }
        .grand-total-value { float: right; font-size: 14px; font-weight: 700; color: #08785c; }
    </style>
</head>
<body>
    @include('admin.pdf.partials.footer', ['label' => 'Reporte de cobradores – confidencial'])
    @include('admin.pdf.partials.header', [
        'kicker'   => 'Recaudación',
        'title'    => 'Reporte de Cobradores',
        'subtitle' => $totalCount . ' cobros registrados',
    ])

    @php
        $methodLabels  = ['cash' => 'Efectivo', 'card' => 'Tarjeta', 'transfer' => 'Transferencia', 'wallet' => 'Billetera'];
        $methodClasses = ['cash' => 'method-cash', 'card' => 'method-card', 'transfer' => 'method-transfer', 'wallet' => 'method-wallet'];
    @endphp

    <div class="ceot-meta">
        <strong>Generado por:</strong> {{ $user->name }}
        | <strong>Cobrador:</strong> {{ $collectorName }}
        | <strong>Periodo:</strong> {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
        @if($dateFrom !== $dateTo)
            al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
        @endif
        | <strong>Fecha de emisión:</strong> {{ now()->format('d/m/Y H:i') }}
    </div>

    {{-- Resumen --}}
    <div class="ceot-section">Resumen</div>
    <table class="ceot-stats">
        <tr>
            <td>
                <span class="ceot-stat-value">{{ $totalCount }}</span>
                <span class="ceot-stat-label">Cobros realizados</span>
            </td>
            <td>
                <span class="ceot-stat-value" style="color:#08785c">Bs {{ number_format($totalAmount, 2) }}</span>
                <span class="ceot-stat-label">Total recaudado</span>
            </td>
            <td>
                <span class="ceot-stat-value">{{ $grouped->count() }}</span>
                <span class="ceot-stat-label">Cobradores activos</span>
            </td>
        </tr>
    </table>

    {{-- Detalle por cobrador --}}
    <div class="ceot-section">Detalle por cobrador</div>

    @forelse($grouped as $collectorName => $dateGroups)
        @php
            $collectorTotal = $dateGroups->flatten(1)->sum('amount');
            $collectorCount = $dateGroups->flatten(1)->count();
        @endphp
        <div class="collector-header">
            <span class="collector-name">{{ $collectorName }}</span>
            <span class="collector-subtotal">Bs {{ number_format($collectorTotal, 2) }} ({{ $collectorCount }} cobros)</span>
        </div>

        @foreach($dateGroups as $date => $payments)
            <div class="date-row">
                📅 {{ \Carbon\Carbon::parse($date)->translatedFormat('l d M, Y') }}
                — {{ $payments->count() }} cobro{{ $payments->count() > 1 ? 's' : '' }}
                — Bs {{ number_format($payments->sum('amount'), 2) }}
                @php
                    $methodSubtotals = $payments->groupBy('method')->map->sum('amount');
                @endphp
                <span style="float: right; font-weight: normal; color: #71869a;">
                    @foreach($methodSubtotals as $m => $mAmount)
                        <span style="margin-left: 8px;">{{ $methodLabels[$m] ?? $m }}: Bs {{ number_format($mAmount, 2) }}</span>
                    @endforeach
                </span>
            </div>

            <table class="ceot-table" style="margin-top:0; margin-bottom:4px;">
                <thead>
                    <tr>
                        <th style="width:10%">Hora</th>
                        <th style="width:14%">Recibo</th>
                        <th>Paciente</th>
                        <th class="text-right" style="width:14%">Monto</th>
                        <th style="width:14%">Método</th>
                        <th style="width:16%">Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->paid_at->format('d/m/Y H:i') }}</td>
                            <td class="font-bold">#{{ $payment->invoice->number ?? '—' }}</td>
                            <td>{{ $payment->invoice->patient->first_name ?? '' }} {{ $payment->invoice->patient->last_name ?? '' }}</td>
                            <td class="text-right" style="color:#08785c; font-weight:700">Bs {{ number_format($payment->amount, 2) }}</td>
                            <td>
                                <span class="method-badge {{ $methodClasses[$payment->method] ?? '' }}">
                                    {{ $methodLabels[$payment->method] ?? $payment->method }}
                                </span>
                            </td>
                            <td style="color:#71869a">{{ $payment->reference ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @empty
        <p style="text-align:center; color:#71869a; padding:20px 0;">No se encontraron cobros para los filtros seleccionados.</p>
    @endforelse

    {{-- Gran total --}}
    <div class="grand-total">
        <span class="grand-total-label">TOTAL GENERAL RECAUDADO</span>
        <span class="grand-total-value">Bs {{ number_format($totalAmount, 2) }}</span>
        <div style="clear:both"></div>
        @if(!empty($subtotalsByMethod))
        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #bbf7d0; font-size: 11px;">
            <strong style="color: #14532d;">Desglose por método de pago:</strong>
            <ul style="margin: 4px 0 0 16px; padding: 0; color: #08785c;">
                @foreach($subtotalsByMethod as $method => $amount)
                    <li>{{ $methodLabels[$method] ?? $method }}: <strong>Bs {{ number_format($amount, 2) }}</strong></li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

</body>
</html>
