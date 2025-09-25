<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Consentimiento</title>
  <style>
    body{ font-family: DejaVu Sans, sans-serif; font-size:12px; line-height:1.4; color:#111; }
    .h1{ font-size:18px; font-weight:700; margin-bottom:6px }
    .muted{ color:#666 }
    .box{ border:1px solid #ddd; padding:12px; border-radius:6px; }
    .sigrow{ margin-top:40px; display:flex; gap:40px }
    .sig{ flex:1; text-align:center }
    .line{ margin-top:40px; border-top:1px solid #333; }
  </style>
</head>
<body>
  <div class="h1">{{ $consent->title }}</div>
  <div class="muted">
    Paciente: <strong>{{ $consent->patient->last_name }}, {{ $consent->patient->first_name }}</strong><br>
    Fecha: {{ now()->toDateString() }}
  </div>

  <div style="height:12px"></div>
  <div class="box">{!! nl2br(e($consent->body)) !!}</div>

  <div class="sigrow">
    <div class="sig">
      <div class="line"></div>
      <div>Firma del paciente</div>
      <div class="muted">{{ $consent->patient->first_name }} {{ $consent->patient->last_name }}</div>
    </div>
    <div class="sig">
      <div class="line"></div>
      <div>Firma del profesional</div>
      <div class="muted">&nbsp;</div>
    </div>
  </div>
</body>
</html>
