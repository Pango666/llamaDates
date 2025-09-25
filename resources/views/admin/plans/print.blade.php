<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Plan #{{ $plan->id }}</title>
  <style>
    body{ font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial; font-size:12px; color:#111; }
    .wrap{ max-width:800px; margin:0 auto; }
    h1{ font-size:18px; margin:0 0 6px }
    .muted{ color:#64748b }
    table{ width:100%; border-collapse:collapse; margin-top:10px }
    th,td{ border:1px solid #e5e7eb; padding:6px 8px; text-align:left }
    th{ background:#f8fafc }
    .right{ text-align:right }
    .badge{ font-size:11px; padding:2px 6px; border-radius:6px; display:inline-block }
    .b-draft{ background:#f1f5f9 } .b-approved{ background:#dcfce7 } .b-in{ background:#dbeafe }
    .tot{ font-weight:600 }
    .footer{ margin-top:20px; font-size:11px; color:#64748b }
    @media print { .no-print{ display:none } }
  </style>
</head>
<body>
<div class="wrap">
  <div class="no-print" style="text-align:right;margin:12px 0">
    <button onclick="window.print()">Imprimir</button>
  </div>

  <h1>Plan de tratamiento #{{ $plan->id }}</h1>
  <div class="muted">
    Paciente: <strong>{{ $plan->patient->last_name }}, {{ $plan->patient->first_name }}</strong><br>
    Título: <strong>{{ $plan->title }}</strong><br>
    Estado:
    @php $b = ['draft'=>'b-draft','approved'=>'b-approved','in_progress'=>'b-in'][$plan->status] ?? 'b-draft'; @endphp
    <span class="badge {{ $b }}">{{ str_replace('_',' ',$plan->status) }}</span>
    @if($plan->approved_at)
      · Aprobado: {{ $plan->approved_at->format('Y-m-d H:i') }} por {{ $plan->approver?->name }}
    @endif
  </div>

  <table>
    <thead>
      <tr>
        <th>Servicio</th>
        <th>Pieza</th>
        <th>Sup.</th>
        <th class="right">Precio (Bs)</th>
        <th>Estado</th>
        <th>Notas</th>
      </tr>
    </thead>
    <tbody>
      @foreach($plan->treatments as $t)
        <tr>
          <td>{{ $t->service?->name ?? '—' }}</td>
          <td>{{ $t->tooth_code ?: '—' }}</td>
          <td>{{ $t->surface ?: '—' }}</td>
          <td class="right">{{ number_format($t->price,2) }}</td>
          <td>{{ str_replace('_',' ',$t->status) }}</td>
          <td>{{ $t->notes }}</td>
        </tr>
      @endforeach
      <tr>
        <td colspan="3" class="right tot">TOTAL</td>
        <td class="right tot">{{ number_format($plan->estimate_total,2) }}</td>
        <td colspan="2"></td>
      </tr>
    </tbody>
  </table>

  <div class="footer">
    Generado el {{ now()->format('Y-m-d H:i') }} · Sistema
  </div>
</div>
</body>
</html>
