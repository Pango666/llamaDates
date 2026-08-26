<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de pagos</title>
    @include('admin.pdf.partials.styles')
    <style>
        .invoice-items { margin-top: 4px; color: #71869a; font-size: 8px; }
        .invoice-items div { padding-top: 2px; }
    </style>
</head>
<body>
    @include('admin.pdf.partials.footer', ['label' => 'Reporte financiero confidencial'])
    @include('admin.pdf.partials.header', [
        'kicker' => 'Control financiero',
        'title' => 'Reporte de pagos',
        'subtitle' => $invoices->count().' comprobantes incluidos',
    ])

    <div class="ceot-meta">
        <strong>Generado por:</strong> {{ $user->name }}
        @if($filters['from'] || $filters['to'])
            | <strong>Periodo:</strong> {{ $filters['from'] ?? 'Inicio' }} al {{ $filters['to'] ?? 'Fin' }}
        @else
            | <strong>Periodo:</strong> Histórico
        @endif
        @if($filters['status'] !== 'all') | <strong>Estado:</strong> {{ ucfirst($filters['status']) }} @endif
        @if($filters['q']) | <strong>Búsqueda:</strong> “{{ $filters['q'] }}” @endif
    </div>

    <div class="ceot-section">Resumen financiero</div>
    <table class="ceot-stats">
        <tr>
            <td><span class="ceot-stat-value">Bs {{ number_format($totalInvoiced, 2) }}</span><span class="ceot-stat-label">Total facturado</span></td>
            <td><span class="ceot-stat-value" style="color:#08785c">Bs {{ number_format($totalPaid, 2) }}</span><span class="ceot-stat-label">Total recaudado</span></td>
            <td><span class="ceot-stat-value" style="color:#956000">Bs {{ number_format($totalPending, 2) }}</span><span class="ceot-stat-label">Por cobrar</span></td>
        </tr>
    </table>

    {{-- Desglose por método de pago --}}
    @if(isset($paymentsByMethod) && $paymentsByMethod->count())
    @php
        $methodLabels = ['cash' => 'Efectivo', 'card' => 'Tarjeta', 'transfer' => 'Transferencia', 'wallet' => 'Billetera Digital'];
        $methodColors = ['cash' => '#16a34a', 'card' => '#7c3aed', 'transfer' => '#0284c7', 'wallet' => '#ea580c'];
    @endphp
    <div style="margin: 10px 0 16px; padding: 10px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
        <div style="font-size: 10px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 8px;">
            Desglose por método de pago
        </div>
        <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
            @foreach($paymentsByMethod as $pm)
                <tr>
                    <td style="padding: 5px 10px; border-bottom: 1px solid #f1f5f9; width: 40%;">
                        <span style="display: inline-block; width: 4px; height: 12px; background: {{ $methodColors[$pm->method] ?? '#94a3b8' }}; border-radius: 2px; margin-right: 8px; vertical-align: middle;"></span>
                        <span style="font-weight: 600; color: #334155;">{{ $methodLabels[$pm->method] ?? ucfirst($pm->method) }}</span>
                    </td>
                    <td style="padding: 5px 10px; border-bottom: 1px solid #f1f5f9; color: #64748b; width: 20%;">
                        {{ $pm->count }} {{ $pm->count === 1 ? 'cobro' : 'cobros' }}
                    </td>
                    <td style="padding: 5px 10px; border-bottom: 1px solid #f1f5f9; text-align: right; font-weight: 700; color: {{ $methodColors[$pm->method] ?? '#334155' }};">
                        Bs {{ number_format($pm->total, 2) }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td style="padding: 7px 10px; font-weight: 700; color: #0f172a;" colspan="2">
                    TOTAL RECAUDADO
                </td>
                <td style="padding: 7px 10px; text-align: right; font-weight: 700; font-size: 12px; color: #08785c;">
                    Bs {{ number_format($totalPaid, 2) }}
                </td>
            </tr>
        </table>
    </div>
    @endif

    @php
        $statusLabels = ['paid'=>'Pagado', 'issued'=>'Pendiente', 'draft'=>'Borrador', 'canceled'=>'Cancelado'];
        $statusClasses = ['paid'=>'badge-green', 'issued'=>'badge-amber', 'draft'=>'badge-slate', 'canceled'=>'badge-red'];
    @endphp
    <div class="ceot-section">Detalle de comprobantes</div>
    <table class="ceot-table">
        <thead>
            <tr>
                <th style="width:12%">Fecha</th><th style="width:15%">Número</th><th>Paciente y servicios</th>
                <th class="text-right" style="width:12%">Total</th><th class="text-right" style="width:12%">Pagado</th>
                <th class="text-right" style="width:12%">Saldo</th><th style="width:10%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->created_at->format('d/m/Y H:i') }}</td>
                    <td class="font-bold">{{ $invoice->number }}</td>
                    <td>
                        <span class="font-bold">{{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}</span>
                        @if($invoice->items->count())
                            <div class="invoice-items">
                                @foreach($invoice->items as $item)
                                    <div>{{ $item->quantity }}x {{ $item->description ?: ($item->service->name ?? 'Servicio') }} - Bs {{ number_format($item->total, 2) }}</div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($invoice->calc_total, 2) }}</td>
                    <td class="text-right" style="color:#08785c">{{ number_format($invoice->calc_paid, 2) }}</td>
                    <td class="text-right" style="color:{{ $invoice->calc_balance > 0.01 ? '#956000' : '#536578' }}">{{ number_format($invoice->calc_balance, 2) }}</td>
                    <td><span class="ceot-badge {{ $statusClasses[$invoice->status] ?? 'badge-slate' }}">{{ $statusLabels[$invoice->status] ?? $invoice->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center muted">No se encontraron comprobantes con los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
