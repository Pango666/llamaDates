<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Factura #{{ $invoice->number }}</title>
  <style>
    *{ box-sizing: border-box; }
    body{ font-family: DejaVu Sans, sans-serif; font-size:12px; color:#111; margin:0; padding:24px; }
    .row{ display:flex; gap:16px; }
    .col{ flex:1; }
    .h{ font-weight:700; }
    .muted{ color:#6b7280; }
    .card{ border:1px solid #e5e7eb; border-radius:8px; padding:12px; }
    .title{ font-size:18px; font-weight:700; margin:0 0 4px }
    .badge{ display:inline-block; font-size:11px; padding:2px 6px; border-radius:6px; }
    .b-paid{ background:#d1fae5; color:#065f46; }
    .b-issued{ background:#fef3c7; color:#92400e; }
    .b-canceled{ background:#e5e7eb; color:#374151; text-decoration: line-through; }
    table{ width:100%; border-collapse: collapse; }
    th, td{ padding:8px; border-bottom:1px solid #e5e7eb; text-align:left; }
    th{ background:#f8fafc; font-weight:700; }
    .tr{ text-align:right; }
    .totals td{ border:none; padding:4px 0; }
    .totals .label{ color:#374151 }
    .totals .val{ text-align:right; }
    .grand{ font-weight:700; }
    .footer{ margin-top:16px; font-size:11px; color:#6b7280; }
  </style>
</head>
<body>
  <div class="row" style="align-items:flex-start;margin-bottom:12px">
    <div class="col">
      <div class="title">Factura #{{ $invoice->number }}</div>
      <div class="muted">Emitida: {{ $invoice->issued_at?->format('Y-m-d H:i') ?? '—' }}</div>
      <div style="margin-top:6px">
        @php
          $badgeClass = $invoice->status==='paid' ? 'b-paid' : ($invoice->status==='canceled' ? 'b-canceled' : 'b-issued');
        @endphp
        <span class="badge {{ $badgeClass }}">{{ strtoupper($invoice->status) }}</span>
      </div>
    </div>
    <div class="col card">
      <div class="h">Paciente</div>
      <div>{{ $invoice->patient->last_name }}, {{ $invoice->patient->first_name }}</div>
      @if($invoice->patient->ci)<div class="muted">CI: {{ $invoice->patient->ci }}</div>@endif
      @if($invoice->patient->phone)<div class="muted">Tel: {{ $invoice->patient->phone }}</div>@endif
      @if($invoice->patient->email)<div class="muted">Email: {{ $invoice->patient->email }}</div>@endif
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Descripción</th>
        <th>Cant.</th>
        <th class="tr">P. unitario</th>
        <th class="tr">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->items as $it)
        <tr>
          <td>{{ $it->description }}</td>
          <td>{{ $it->quantity }}</td>
          <td class="tr">Bs {{ number_format($it->unit_price,2) }}</td>
          <td class="tr">Bs {{ number_format($it->total,2) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <table class="totals" style="margin-top:8px">
    <tr>
      <td class="label" style="width:70%">Subtotal</td>
      <td class="val">Bs {{ number_format($subtotal,2) }}</td>
    </tr>
    <tr>
      <td class="label">Descuento</td>
      <td class="val">- Bs {{ number_format($invoice->discount,2) }}</td>
    </tr>
    <tr>
      <td class="label">Impuesto ({{ $invoice->tax_percent }}%)</td>
      <td class="val">Bs {{ number_format($tax,2) }}</td>
    </tr>
    <tr class="grand">
      <td class="label">TOTAL</td>
      <td class="val">Bs {{ number_format($grand,2) }}</td>
    </tr>
    <tr>
      <td class="label">Pagado</td>
      <td class="val">Bs {{ number_format($paid,2) }}</td>
    </tr>
    <tr>
      <td class="label">Saldo</td>
      <td class="val">Bs {{ number_format($balance,2) }}</td>
    </tr>
  </table>

  @if($invoice->notes)
    <div class="card" style="margin-top:12px">
      <div class="h" style="margin-bottom:4px">Notas</div>
      <div>{{ $invoice->notes }}</div>
    </div>
  @endif

  @if($invoice->payments->count())
    <div class="card" style="margin-top:12px">
      <div class="h" style="margin-bottom:4px">Pagos</div>
      <table>
        <thead>
          <tr><th>Método</th><th>Ref</th><th>Fecha</th><th class="tr">Monto</th></tr>
        </thead>
        <tbody>
          @foreach($invoice->payments as $p)
            <tr>
              <td>{{ ucfirst($p->method) }}</td>
              <td>{{ $p->reference ?: '—' }}</td>
              <td>{{ $p->paid_at?->format('Y-m-d H:i') ?? '—' }}</td>
              <td class="tr">Bs {{ number_format($p->amount,2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif

  <div class="footer">
    generado por llamaDates · {{ now()->format('Y-m-d H:i') }}
  </div>
</body>
</html>
