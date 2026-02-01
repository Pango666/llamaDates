<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Pagos</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        h1 { margin: 0; font-size: 18px; color: #1e293b; }
        .meta { font-size: 10px; color: #64748b; margin-top: 5px; }

        .summary-box { margin-bottom: 20px; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; }
        .grid { width: 100%; display: table; table-layout: fixed; }
        .col { display: table-cell; text-align: center; vertical-align: middle; }
        
        .val { font-size: 14px; font-weight: bold; display: block; }
        .lbl { font-size: 9px; color: #64748b; text-transform: uppercase; margin-top: 2px; display: block; }
        
        .text-blue { color: #2563eb; }
        .text-green { color: #059669; }
        .text-amber { color: #d97706; }

        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 5px 6px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; color: #475569; }
        td.num { text-align: right; }

        .badge { padding: 2px 4px; border-radius: 4px; font-size: 8px; font-weight: bold; }
        .st-paid { background: #d1fae5; color: #065f46; }
        .st-issued { background: #dbeafe; color: #1e40af; }
        .st-draft { background: #f1f5f9; color: #475569; }
        .st-canceled { background: #fee2e2; color: #991b1b; text-decoration: line-through; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 8px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 5px; }
    </style>
</head>
<body>
    <header>
        <h1>Reporte de Pagos y Recibos</h1>
        <div class="meta">
            Generado: {{ now()->format('d/m/Y H:i') }} | Usuario: {{ $user->name }} <br>
            Filtros: 
            @if($filters['from'] || $filters['to']) Fecha: {{ $filters['from'] ?? 'Inicio' }} - {{ $filters['to'] ?? 'Fin' }} @endif
            @if($filters['status'] !== 'all') | Estado: {{ ucfirst($filters['status']) }} @endif
            @if($filters['q']) | Busqueda: "{{ $filters['q'] }}" @endif
        </div>
    </header>

    <div class="summary-box">
        <div class="grid">
            <div class="col">
                <span class="val text-blue">Bs {{ number_format($totalInvoiced, 2) }}</span>
                <span class="lbl">Total Facturado</span>
            </div>
            <div class="col">
                <span class="val text-green">Bs {{ number_format($totalPaid, 2) }}</span>
                <span class="lbl">Total Recaudado</span>
            </div>
            <div class="col">
                <span class="val text-amber">Bs {{ number_format($totalPending, 2) }}</span>
                <span class="lbl">Por Cobrar</span>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Fecha</th>
                <th style="width: 15%;">Número</th>
                <th>Paciente</th>
                <th style="width: 12%; text-align: right;">Total</th>
                <th style="width: 12%; text-align: right;">Pagado</th>
                <th style="width: 12%; text-align: right;">Saldo</th>
                <th style="width: 10%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $inv)
                <tr>
                    <td>{{ $inv->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $inv->number }}</td>
                    <td>
                        {{ $inv->patient->first_name }} {{ $inv->patient->last_name }}
                    </td>
                    <td class="num">{{ number_format($inv->calc_total, 2) }}</td>
                    <td class="num" style="color: #059669;">{{ number_format($inv->calc_paid, 2) }}</td>
                    <td class="num" style="color: {{ $inv->calc_balance > 0.01 ? '#d97706' : '#64748b' }};">
                        {{ number_format($inv->calc_balance, 2) }}
                    </td>
                    <td>
                        @php
                            $labels = ['paid'=>'PAGADO', 'issued'=>'PENDIENTE', 'draft'=>'BORRADOR', 'canceled'=>'CANCELADO'];
                            $st = $inv->status;
                        @endphp
                        <span class="badge st-{{ $st }}">{{ $labels[$st] ?? strtoupper($st) }}</span>
                    </td>
                </tr>
                {{-- Detalles (Items) --}}
                @if($inv->items->count() > 0)
                    <tr style="background-color: #f8fafc;">
                        <td colspan="7" style="padding: 0 0 5px 0;">
                            <table style="width: 95%; margin: 0 auto; border: none;">
                                @foreach($inv->items as $item)
                                    <tr>
                                        <td style="border: none; border-bottom: 1px dashed #e2e8f0; font-size: 9px; padding: 2px 0; color: #64748b;">
                                            - {{ $item->quantity }}x {{ $item->description }} 
                                            @if($item->service) <span style="font-style: italic;">({{ $item->service->name }})</span> @endif
                                        </td>
                                        <td style="border: none; border-bottom: 1px dashed #e2e8f0; font-size: 9px; padding: 2px 0; text-align: right; color: #64748b; width: 60px;">
                                            {{ number_format($item->total, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: #94a3b8;">
                        No se encontraron registros con los filtros actuales.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        DentalCare System - Reporte Financiero - {{ $invoices->count() }} registros listados.
    </div>
</body>
</html>
