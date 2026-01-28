<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo #{{ $invoice->number }}</title>
    <style>
        @page { margin: 0; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 13px; 
            color: #334155; 
            margin: 0; 
            padding: 0; 
            line-height: 1.5;
        }

        /* Layout & Colors */
        .header-bg {
            background-color: #2563eb; /* Blue 600 */
            color: white;
            padding: 40px 50px;
        }
        
        .main-content {
            padding: 30px 50px;
        }

        .brand-name { font-size: 26px; font-weight: bold; letter-spacing: -0.5px; }
        .brand-sub { opacity: 0.9; font-size: 13px; margin-top: 5px; }

        .recibo-badge {
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            display: inline-block;
            text-align: right;
        }

        .flex-between { display: table; width: 100%; }
        .col-left { display: table-cell; vertical-align: top; width: 60%; }
        .col-right { display: table-cell; vertical-align: top; width: 40%; text-align: right; }

        /* Patient & Info Cards */
        .info-section { margin-top: 30px; margin-bottom: 30px; }
        .info-label { 
            color: #64748b; 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            font-weight: bold; 
            margin-bottom: 4px;
        }
        .info-value { font-size: 14px; color: #0f172a; font-weight: 500; }
        .info-sub { font-size: 12px; color: #64748b; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { 
            text-align: left; 
            padding: 12px 15px; 
            background-color: #f1f5f9; 
            color: #475569; 
            font-weight: bold; 
            font-size: 11px; 
            text-transform: uppercase; 
            border-bottom: 2px solid #e2e8f0;
        }
        td { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        tr:last-child td { border-bottom: none; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        /* Totals */
        .totals-section { margin-top: 40px; page-break-inside: avoid; }
        .totals-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            width: 100%;
        }
        .totals-table td { padding: 5px 0; border: none; }
        .total-row td { 
            padding-top: 12px; 
            border-top: 2px solid #e2e8f0; 
            font-size: 16px; 
            font-weight: bold; 
            color: #2563eb;
        }

        .status-paid { 
            color: #059669; 
            background: #d1fae5; 
            padding: 4px 10px; 
            border-radius: 4px; 
            font-weight: bold; 
            font-size: 11px; 
            text-transform: uppercase; 
            display: inline-block;
        }
        .status-pending { 
            color: #d97706; 
            background: #fef3c7; 
            padding: 4px 10px; 
            border-radius: 4px; 
            font-weight: bold; 
            font-size: 11px; 
            text-transform: uppercase;
            display: inline-block;
        }

        .footer {
            position: fixed;
            bottom: 40px;
            left: 50px;
            right: 50px;
            text-align: center;
            color: #94a3b8;
            font-size: 11px;
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    @php
        // Recalcular totales para asegurar precisión si la DB tiene 0
        $calcSubtotal = 0;
        foreach($invoice->items as $item) {
            $calcSubtotal += $item->total;
        }
        $discount = (float)$invoice->discount;
        $taxP = (float)$invoice->tax_percent;
        
        $subEx = max(0, $calcSubtotal - $discount);
        $taxAmt = $subEx * ($taxP / 100);
        $calcGrand = $subEx + $taxAmt;

        // Priorizar DB si tiene datos, sino usar calculado
        $finalTotal = $invoice->total > 0 ? $invoice->total : $calcGrand;
        
        // Pagos
        $paidAmt = $invoice->payments->sum('amount');
        $balance = $finalTotal - $paidAmt;

        // Traducciones
        $methods = [
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
            'transfer' => 'Transferencia',
            'wallet' => 'Billetera'
        ];
    @endphp

    <div class="header-bg">
        <div class="flex-between">
            <div class="col-left">
                <div class="brand-name">{{ config('app.name', 'DentalCare') }}</div>
                <div class="brand-sub">Comprobante de Pago</div>
            </div>
            <div class="col-right">
                <div class="recibo-badge">
                    RECIBO #{{ $invoice->number }}
                </div>
                <div style="margin-top: 10px;">
                    @if($invoice->status === 'paid' || $balance <= 0)
                        <span class="status-paid" style="background: white; color: #059669;">PAGADA</span>
                    @else
                        <span class="status-pending" style="background: white; color: #d97706;">PENDIENTE</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        
        <div class="flex-between info-section">
            <div class="col-left">
                <div class="info-label">Paciente</div>
                <div class="info-value">{{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}</div>
                @if($invoice->patient->ci)<div class="info-sub">CI: {{ $invoice->patient->ci }}</div>@endif
                @if($invoice->patient->email)<div class="info-sub">{{ $invoice->patient->email }}</div>@endif
            </div>
            <div class="col-right">
                <div style="margin-bottom: 12px;">
                    <div class="info-label">Fecha de Emisión</div>
                    <div class="info-value">{{ $invoice->issued_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}</div>
                </div>
                <div>
                    <div class="info-label">Generado por</div>
                    <div class="info-value">{{ auth()->user()->name ?? 'Admin' }}</div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="50%">Descripción</th>
                    <th width="15%" class="text-center">Cant.</th>
                    <th width="15%" class="text-right">Precio Unit.</th>
                    <th width="20%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>
                        <div class="font-bold">{{ $item->description ?: ($item->service->name ?? 'Servicio') }}</div>
                        @if($item->details)
                            <div style="font-size: 11px; color: #64748b; margin-top:2px;">{{ $item->details }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-section">
            <div class="flex-between">
                <div class="col-left" style="padding-right: 40px;">
                    @if($invoice->notes)
                        <div class="info-label">Notas</div>
                        <p style="font-size: 12px; color: #475569; background: #f8fafc; padding: 10px; border-radius: 6px;">
                            {{ $invoice->notes }}
                        </p>
                    @endif

                     @if($invoice->payments->count() > 0)
                        <div style="margin-top: 20px;">
                            <div class="info-label">Historial de Pagos</div>
                            @foreach($invoice->payments as $pay)
                                <div style="font-size: 11px; color: #475569; margin-bottom: 4px; display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 2px;">
                                    <span>{{ $pay->created_at->format('d/m/Y') }} - {{ $methods[$pay->method] ?? ucfirst($pay->method) }}</span>
                                    <span style="font-weight: bold; color: #059669;">Bs {{ number_format($pay->amount, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="col-right">
                    <div class="totals-box">
                        <table class="totals-table">
                            <tr>
                                <td class="text-right" style="color:#64748b;">Subtotal</td>
                                <td class="text-right font-bold">Bs {{ number_format($calcSubtotal, 2) }}</td>
                            </tr>
                            @if($discount > 0)
                            <tr>
                                <td class="text-right" style="color:#e11d48;">Descuento</td>
                                <td class="text-right font-bold" style="color:#e11d48;">- Bs {{ number_format($discount, 2) }}</td>
                            </tr>
                            @endif
                            @if($taxP > 0)
                            <tr>
                                <td class="text-right" style="color:#64748b;">Impuesto ({{ $taxP }}%)</td>
                                <td class="text-right font-bold">Bs {{ number_format($taxAmt, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="total-row">
                                <td class="text-right">Total Recibo</td>
                                <td class="text-right">Bs {{ number_format($finalTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right" style="padding-top: 8px; color:#059669;">Pagado</td>
                                <td class="text-right" style="padding-top: 8px; color:#059669; font-weight: bold;">Bs {{ number_format($paidAmt, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right" style="color: {{ $balance > 0 ? '#d97706' : '#64748b' }};">Saldo</td>
                                <td class="text-right font-bold" style="color: {{ $balance > 0 ? '#d97706' : '#64748b' }};">Bs {{ number_format($balance, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        Gracias por su confianza · {{ config('app.name') }}
    </div>

</body>
</html>
