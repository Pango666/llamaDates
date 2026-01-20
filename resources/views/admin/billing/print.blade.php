<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Recibo #{{ $invoice->number }}</title>
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
      color: #111827;
      margin: 0;
      padding: 22px;
      background: #fff;
    }

    h1,h2,h3,h4,h5,h6 { margin: 0; padding: 0; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .text-muted { color: #6b7280; }
    .small { font-size: 11px; }
    .mb-6 { margin-bottom: 6px; }
    .mb-10 { margin-bottom: 10px; }
    .mb-14 { margin-bottom: 14px; }
    .mt-10 { margin-top: 10px; }

    /* Header */
    .header {
      border-bottom: 1px solid #e5e7eb;
      padding-bottom: 10px;
      margin-bottom: 14px;
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
    }
    .header-table td { vertical-align: top; }

    .brand {
      font-size: 16px;
      font-weight: 800;
      letter-spacing: .2px;
      margin-bottom: 2px;
    }
    .doc-title {
      font-size: 18px;
      font-weight: 800;
      text-align: right;
      margin-bottom: 2px;
    }
    .doc-sub { font-size: 11px; color: #4b5563; text-align: right; }

    .badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 9999px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .2px;
    }
    .badge-paid { background: #d1fae5; color: #065f46; }
    .badge-issued { background: #fef3c7; color: #92400e; }
    .badge-canceled { background: #e5e7eb; color: #374151; text-decoration: line-through; }

    /* Cards */
    .grid {
      width: 100%;
      border-collapse: collapse;
    }
    .grid td {
      vertical-align: top;
      padding: 0;
    }

    .card {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 10px 12px;
      margin-bottom: 10px;
    }
    .card-title {
      font-weight: 800;
      font-size: 12.5px;
      margin-bottom: 6px;
    }

    .info-table {
      width: 100%;
      border-collapse: collapse;
    }
    .info-table td {
      padding: 2px 0;
      vertical-align: top;
      font-size: 12px;
    }
    .info-label {
      color: #6b7280;
      width: 90px;
      padding-right: 8px;
    }

    /* Items */
    table.items {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
    }
    table.items th, table.items td {
      padding: 7px 8px;
      border-bottom: 1px solid #e5e7eb;
      font-size: 12px;
    }
    table.items th {
      background: #f9fafb;
      font-weight: 800;
      color: #111827;
    }
    .row-alt { background: #fcfcfd; }

    .money { white-space: nowrap; }
    .desc {
      font-weight: 600;
    }
    .desc-sub {
      font-size: 11px;
      color: #6b7280;
      margin-top: 1px;
    }

    /* Totals */
    .totals-wrap {
      width: 100%;
      margin-top: 12px;
    }
    .totals-box {
      width: 44%;
      min-width: 240px;
      margin-left: auto;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 10px 12px;
    }
    .totals-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
    }
    .totals-table td {
      padding: 3px 0;
    }
    .totals-label { color: #4b5563; }
    .totals-value { text-align: right; }
    .totals-sep td { padding-top: 6px; border-top: 1px dashed #e5e7eb; }
    .grand { font-weight: 900; font-size: 13px; }

    /* Payments */
    .payments-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
      font-size: 12px;
    }
    .payments-table th, .payments-table td {
      padding: 6px 7px;
      border-bottom: 1px solid #e5e7eb;
    }
    .payments-table th {
      background: #f9fafb;
      font-weight: 800;
    }

    /* Footer */
    .footer {
      margin-top: 16px;
      border-top: 1px solid #e5e7eb;
      padding-top: 8px;
      font-size: 11px;
      color: #6b7280;
      text-align: right;
    }
  </style>
</head>

<body>
  @php
    // Badge estado
    $status = (string) ($invoice->status ?? 'issued');
    $badgeClass = 'badge-issued';
    $label = 'EMITIDA';
    if ($status === 'paid') { $badgeClass = 'badge-paid'; $label = 'PAGADA'; }
    elseif ($status === 'canceled') { $badgeClass = 'badge-canceled'; $label = 'ANULADA'; }

    // Cálculos defensivos (por si el controller no pasó variables)
    $items = $invoice->items ?? collect();
    $calcSubtotal = 0.0;
    foreach ($items as $it) {
      $calcSubtotal += (float)($it->total ?? ((float)$it->quantity * (float)$it->unit_price));
    }

    $discount = (float)($invoice->discount ?? 0);
    $taxPercent = (float)($invoice->tax_percent ?? 0);
    $baseAfterDiscount = max(0, $calcSubtotal - $discount);
    $calcTax = round($baseAfterDiscount * ($taxPercent / 100), 2);
    $calcGrand = round($baseAfterDiscount + $calcTax, 2);

    $calcPaid = 0.0;
    if ($invoice->relationLoaded('payments') || isset($invoice->payments)) {
      foreach (($invoice->payments ?? collect()) as $p) $calcPaid += (float)($p->amount ?? 0);
    }
    $calcBalance = round(max(0, $calcGrand - $calcPaid), 2);

    // Si el controller ya mandó subtotal/tax/grand/paid/balance, los respetamos
    $subtotal = isset($subtotal) ? (float)$subtotal : $calcSubtotal;
    $tax      = isset($tax) ? (float)$tax : $calcTax;
    $grand    = isset($grand) ? (float)$grand : $calcGrand;
    $paid     = isset($paid) ? (float)$paid : $calcPaid;
    $balance  = isset($balance) ? (float)$balance : $calcBalance;

    $methodLabels = [
      'cash'     => 'Efectivo',
      'card'     => 'Tarjeta',
      'transfer' => 'Transferencia bancaria',
      'wallet'   => 'Billetera digital',
    ];
  @endphp

  {{-- HEADER --}}
  <div class="header">
    <table class="header-table">
      <tr>
        <td>
          <div class="brand">{{ config('app.name', 'llamaDates') }}</div>
          <div class="text-muted small">Clínica Odontológica</div>
          <div class="text-muted small">Dirección: —</div>
          <div class="text-muted small">Teléfono: —</div>
        </td>
        <td class="text-right">
          <div class="doc-title">Recibo #{{ $invoice->number }}</div>
          <div class="doc-sub">
            Emitido: {{ $invoice->issued_at?->format('d/m/Y H:i') ?? '—' }}
          </div>
          <div style="margin-top:8px;">
            <span class="badge {{ $badgeClass }}">{{ $label }}</span>
          </div>
        </td>
      </tr>
    </table>
  </div>

  {{-- RESUMEN: PACIENTE / FACTURA / CITA --}}
  <table class="grid" style="margin-bottom: 10px;">
    <tr>
      <td style="width: 58%; padding-right: 10px;">
        <div class="card">
          <div class="card-title">Paciente</div>
          <div class="desc">
            {{ $invoice->patient->last_name ?? '—' }}, {{ $invoice->patient->first_name ?? '—' }}
          </div>
          <table class="info-table mt-10">
            @if(!empty($invoice->patient?->ci))
              <tr><td class="info-label">CI:</td><td>{{ $invoice->patient->ci }}</td></tr>
            @endif
            @if(!empty($invoice->patient?->phone))
              <tr><td class="info-label">Tel:</td><td>{{ $invoice->patient->phone }}</td></tr>
            @endif
            @if(!empty($invoice->patient?->email))
              <tr><td class="info-label">Email:</td><td>{{ $invoice->patient->email }}</td></tr>
            @endif
          </table>
        </div>
      </td>

      <td style="width: 42%;">
        <div class="card">
          <div class="card-title">Datos del recibo</div>
          <table class="info-table">
            <tr>
              <td class="info-label">Estado:</td>
              <td>
                {{ ['draft'=>'Borrador','issued'=>'Emitida','paid'=>'Pagada','canceled'=>'Anulada'][$invoice->status] ?? $invoice->status }}
                @if($invoice->paid_at)
                  <div class="text-muted small">Pagada: {{ $invoice->paid_at->format('d/m/Y H:i') }}</div>
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
              </tr>
              <tr>
                <td class="info-label">Atendió:</td>
                <td>{{ $invoice->appointment->dentist->name ?? '—' }}</td>
              </tr>
            @endif
          </table>
        </div>
      </td>
    </tr>
  </table>

  {{-- ITEMS --}}
  <div class="card">
    <div class="card-title">Detalle del recibo</div>

    @if(($invoice->items ?? collect())->count() === 0)
      <div class="text-muted">No hay servicios/ítems registrados en este recibo.</div>
    @else
      <table class="items">
        <thead>
          <tr>
            <th style="width:55%;">Servicio / Descripción</th>
            <th style="width:10%;" class="text-center">Cant.</th>
            <th style="width:17%;" class="text-right">P. unitario</th>
            <th style="width:18%;" class="text-right">Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach($invoice->items as $i => $it)
            @php
              $rowAlt = $i % 2 === 1;
              $qty = (int)($it->quantity ?? 1);
              $unit = (float)($it->unit_price ?? 0);
              $lineTotal = (float)($it->total ?? ($qty * $unit));
            @endphp
            <tr class="{{ $rowAlt ? 'row-alt' : '' }}">
              <td>
                <div class="desc">{{ $it->description ?? '—' }}</div>

                {{-- Si tienes service_id y quieres mostrar algo extra (opcional) --}}
                @if(!empty($it->service_id))
                  <div class="desc-sub">Servicio ID: {{ $it->service_id }}</div>
                @endif
              </td>
              <td class="text-center">{{ $qty }}</td>
              <td class="text-right money">Bs {{ number_format($unit, 2) }}</td>
              <td class="text-right money">Bs {{ number_format($lineTotal, 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  {{-- TOTALES --}}
  <div class="totals-wrap">
    <div class="totals-box">
      <table class="totals-table">
        <tr>
          <td class="totals-label">Subtotal</td>
          <td class="totals-value money">Bs {{ number_format($subtotal, 2) }}</td>
        </tr>
        <tr>
          <td class="totals-label">Descuento</td>
          <td class="totals-value money">- Bs {{ number_format((float)$invoice->discount, 2) }}</td>
        </tr>
        <tr>
          <td class="totals-label">Impuesto ({{ number_format((float)$invoice->tax_percent, 2) }}%)</td>
          <td class="totals-value money">Bs {{ number_format($tax, 2) }}</td>
        </tr>

        <tr class="totals-sep">
          <td class="totals-label grand">Total</td>
          <td class="totals-value grand money">Bs {{ number_format($grand, 2) }}</td>
        </tr>
        <tr>
          <td class="totals-label">Pagado</td>
          <td class="totals-value money">Bs {{ number_format($paid, 2) }}</td>
        </tr>
        <tr>
          <td class="totals-label">Saldo</td>
          <td class="totals-value money">Bs {{ number_format($balance, 2) }}</td>
        </tr>
      </table>
    </div>
  </div>

  {{-- NOTAS --}}
  @if(!empty($invoice->notes))
    <div class="card mt-10">
      <div class="card-title">Notas</div>
      <div>{{ $invoice->notes }}</div>
    </div>
  @endif

  {{-- PAGOS --}}
  @if(($invoice->payments ?? collect())->count())
    <div class="card mt-10">
      <div class="card-title">Pagos registrados</div>
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
          @foreach($invoice->payments as $p)
            <tr>
              <td>{{ $methodLabels[$p->method] ?? ucfirst((string)$p->method) }}</td>
              <td>{{ $p->reference ?: '—' }}</td>
              <td>{{ $p->paid_at?->format('d/m/Y H:i') ?? '—' }}</td>
              <td class="text-right money">Bs {{ number_format((float)$p->amount, 2) }}</td>
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
