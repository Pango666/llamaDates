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
