<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Recibo #{{ $invoice->number }}</title>
  <style>
    *{ box-sizing:border-box; }
    body{
      font-family: DejaVu Sans, sans-serif;
      font-size:12px;
      color:#111827;
      margin:0;
      padding:24px;
    }

    h1,h2,h3,h4,h5,h6{ margin:0; padding:0; }

    .text-right{ text-align:right; }
    .text-center{ text-align:center; }
    .text-muted{ color:#6b7280; }

    .header{
      border-bottom:1px solid #e5e7eb;
      padding-bottom:8px;
      margin-bottom:16px;
    }

    .header-table{
      width:100%;
      border-collapse:collapse;
    }
    .header-table td{ vertical-align:top; }

    .clinic-name{
      font-size:16px;
      font-weight:bold;
      margin-bottom:4px;
    }

    .doc-title{
      font-size:18px;
      font-weight:700;
      text-align:right;
    }
    .doc-sub{
      font-size:12px;
      color:#4b5563;
      text-align:right;
    }

    .badge{
      display:inline-block;
      padding:2px 8px;
      border-radius:9999px;
      font-size:11px;
      font-weight:600;
    }
    .badge-paid{ background:#d1fae5; color:#065f46; }
    .badge-issued{ background:#fef3c7; color:#92400e; }
    .badge-canceled{ background:#e5e7eb; color:#374151; text-decoration:line-through; }

    .section-title{
      font-weight:600;
      margin-bottom:4px;
      font-size:13px;
    }

    .card{
      border:1px solid #e5e7eb;
      border-radius:6px;
      padding:8px 10px;
      margin-bottom:10px;
    }

    .info-table{
      width:100%;
      border-collapse:collapse;
      margin-bottom:10px;
    }
    .info-table td{
      padding:2px 0;
      vertical-align:top;
      font-size:12px;
    }
    .info-label{
      color:#6b7280;
      width:90px;
    }

    table.items{
      width:100%;
      border-collapse:collapse;
      margin-top:4px;
    }
    table.items th,
    table.items td{
      padding:6px 8px;
      border-bottom:1px solid #e5e7eb;
      font-size:12px;
    }
    table.items th{
      background:#f9fafb;
      font-weight:600;
    }

    .totals-wrapper{
      margin-top:12px;
      width:100%;
    }
    .totals-table{
      width:40%;
      min-width:220px;
      margin-left:auto;
      border-collapse:collapse;
      font-size:12px;
    }
    .totals-table td{
      padding:2px 0;
    }
    .totals-label{
      color:#4b5563;
    }
    .totals-value{
      text-align:right;
      white-space:nowrap;
    }
    .totals-grand .totals-label{
      font-weight:600;
    }
    .totals-grand .totals-value{
      font-weight:700;
    }

    .payments-table{
      width:100%;
      border-collapse:collapse;
      margin-top:4px;
      font-size:12px;
    }
    .payments-table th,
    .payments-table td{
      padding:5px 6px;
      border-bottom:1px solid #e5e7eb;
    }
    .payments-table th{
      background:#f9fafb;
      font-weight:600;
    }

    .mt-8{ margin-top:8px; }
    .mt-12{ margin-top:12px; }

    .footer{
      margin-top:18px;
      border-top:1px solid #e5e7eb;
      padding-top:6px;
      font-size:11px;
      color:#6b7280;
      text-align:right;
    }
  </style>
</head>
<body>
  {{-- CABECERA --}}
  <div class="header">
    <table class="header-table">
      <tr>
        <td>
          <div class="clinic-name">{{ config('app.name', 'llamaDates') }}</div>
          <div class="text-muted">Clínica Odontológica</div>
          {{-- Puedes cambiar estos textos por datos reales de la clínica --}}
          <div class="text-muted">Dirección: —</div>
          <div class="text-muted">Teléfono: —</div>
        </td>
        <td class="text-right">
          <div class="doc-title">Recibo #{{ $invoice->number }}</div>
          <div class="doc-sub">
            Emitido: {{ $invoice->issued_at?->format('d/m/Y H:i') ?? '—' }}
          </div>
          <div style="margin-top:6px;">
            @php
              $status = $invoice->status;
              $badgeClass = 'badge-issued';
              $label = 'EMITIDA';
              if ($status === 'paid') { $badgeClass = 'badge-paid'; $label = 'PAGADA'; }
              elseif ($status === 'canceled') { $badgeClass = 'badge-canceled'; $label = 'ANULADA'; }
            @endphp
            <span class="badge {{ $badgeClass }}">{{ $label }}</span>
          </div>
        </td>
      </tr>
    </table>
  </div>

  {{-- DATOS DEL PACIENTE Y FACTURA --}}
  <table class="info-table">
    <tr>
      <td class="info-label">Paciente:</td>
      <td>
        {{ $invoice->patient->last_name }}, {{ $invoice->patient->first_name }}<br>
        @if($invoice->patient->ci)
          <span class="text-muted">CI: {{ $invoice->patient->ci }}</span><br>
        @endif
        @if($invoice->patient->phone)
          <span class="text-muted">Tel: {{ $invoice->patient->phone }}</span><br>
        @endif
        @if($invoice->patient->email)
          <span class="text-muted">Email: {{ $invoice->patient->email }}</span>
        @endif
      </td>
      <td class="info-label">Estado:</td>
      <td>
        {{ ['draft'=>'Borrador','issued'=>'Emitida','paid'=>'Pagada','canceled'=>'Anulada'][$invoice->status] ?? $invoice->status }}
        @if($invoice->paid_at)
          <br><span class="text-muted">Pagada: {{ $invoice->paid_at->format('d/m/Y H:i') }}</span>
        @endif
      </td>
    </tr>
    @if($invoice->appointment)
      <tr>
        <td class="info-label">Cita:</td>
        <td>
          #{{ $invoice->appointment->id }}
          @if($invoice->appointment->date)
            · {{ \Illuminate\Support\Carbon::parse($invoice->appointment->date)->format('d/m/Y') }}
          @endif
        </td>
        <td class="info-label">Atendió:</td>
        <td>
          {{ $invoice->appointment->dentist->name ?? '—' }}
        </td>
      </tr>
    @endif
  </table>

  {{-- ÍTEMS --}}
  <div class="card">
    <div class="section-title">Detalle del recibo</div>
    <table class="items">
      <thead>
        <tr>
          <th style="width:55%;">Descripción</th>
          <th style="width:10%;" class="text-center">Cant.</th>
          <th style="width:17%;" class="text-right">P. unitario</th>
          <th style="width:18%;" class="text-right">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($invoice->items as $it)
          <tr>
            <td>{{ $it->description }}</td>
            <td class="text-center">{{ $it->quantity }}</td>
            <td class="text-right">Bs {{ number_format($it->unit_price,2) }}</td>
            <td class="text-right">Bs {{ number_format($it->total,2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- TOTALES --}}
  <div class="totals-wrapper">
    <table class="totals-table">
      <tr>
        <td class="totals-label">Subtotal</td>
        <td class="totals-value">Bs {{ number_format($subtotal,2) }}</td>
      </tr>
      <tr>
        <td class="totals-label">Descuento</td>
        <td class="totals-value">- Bs {{ number_format($invoice->discount,2) }}</td>
      </tr>
      <tr>
        <td class="totals-label">
          Impuesto ({{ number_format($invoice->tax_percent,2) }}%)
        </td>
        <td class="totals-value">Bs {{ number_format($tax,2) }}</td>
      </tr>
      <tr class="totals-grand">
        <td class="totals-label">Total</td>
        <td class="totals-value">Bs {{ number_format($grand,2) }}</td>
      </tr>
      <tr>
        <td class="totals-label">Pagado</td>
        <td class="totals-value">Bs {{ number_format($paid,2) }}</td>
      </tr>
      <tr>
        <td class="totals-label">Saldo</td>
        <td class="totals-value">Bs {{ number_format($balance,2) }}</td>
      </tr>
    </table>
  </div>

  {{-- NOTAS --}}
  @if($invoice->notes)
    <div class="card mt-12">
      <div class="section-title">Notas</div>
      <div>{{ $invoice->notes }}</div>
    </div>
  @endif

  {{-- PAGOS --}}
  @if($invoice->payments->count())
    <div class="card mt-8">
      <div class="section-title">Pagos registrados</div>
      <table class="payments-table">
        <thead>
          <tr>
            <th style="width:20%;">Método</th>
            <th style="width:25%;">Referencia</th>
            <th style="width:25%;">Fecha</th>
            <th style="width:15%;" class="text-right">Monto</th>
          </tr>
        </thead>
        <tbody>
          @php
  $methodLabels = [
    'cash'     => 'Efectivo',
    'card'     => 'Tarjeta',
    'transfer' => 'Transferencia bancaria',
    'wallet'   => 'Billetera digital',
  ];
@endphp

@foreach($invoice->payments as $p)
  <tr>
    <td>{{ $methodLabels[$p->method] ?? ucfirst($p->method) }}</td>
    <td>{{ $p->reference ?: '—' }}</td>
    <td>{{ $p->paid_at?->format('d/m/Y H:i') ?? '—' }}</td>
    <td class="text-right">Bs {{ number_format($p->amount,2) }}</td>
  </tr>
@endforeach
        </tbody>
      </table>
    </div>
  @endif

  <div class="footer">
    Generado por {{ config('app.name', 'llamaDates') }} · {{ now()->format('d/m/Y H:i') }}
  </div>
</body>
</html>
