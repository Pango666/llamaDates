<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $consent->title }}</title>
    @include('admin.pdf.partials.styles')
    <style>
        .consent-body { min-height: 330px; padding: 18px; font-size: 11px; line-height: 1.75; text-align: justify; }
        .signature-table { width: 100%; margin-top: 50px; border-collapse: separate; border-spacing: 24px 0; page-break-inside: avoid; }
        .signature-table td { width: 50%; padding: 32px 4px 0; border: 0; border-top: 1px solid #536578; text-align: center; }
        .signature-title { color: #20384e; font-weight: 700; }
    </style>
</head>
<body>
    @include('admin.pdf.partials.footer', ['label' => 'Consentimiento informado confidencial'])
    @include('admin.pdf.partials.header', [
        'kicker' => 'Consentimiento informado',
        'title' => $consent->title,
        'subtitle' => 'Documento clínico confidencial',
    ])

    <div class="ceot-meta">
        <strong>Paciente:</strong> {{ $consent->patient->last_name }}, {{ $consent->patient->first_name }}
        @if($consent->patient->ci) | <strong>CI:</strong> {{ $consent->patient->ci }} @endif
        | <strong>Fecha:</strong> {{ now()->format('d/m/Y') }}
        @if($consent->appointment?->dentist) | <strong>Profesional:</strong> {{ $consent->appointment->dentist->name }} @endif
    </div>

    <div class="ceot-section">Declaración y autorización</div>
    <div class="ceot-note consent-body">{!! nl2br(e($html ?? $consent->body)) !!}</div>

    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-title">Firma del paciente</div>
                <div class="muted">{{ $consent->patient->first_name }} {{ $consent->patient->last_name }}</div>
                @if($consent->signed_by_doc)<div class="small muted">CI: {{ $consent->signed_by_doc }}</div>@endif
            </td>
            <td>
                <div class="signature-title">Firma del profesional</div>
                <div class="muted">{{ $consent->appointment?->dentist?->name ?? 'Nombre y sello' }}</div>
            </td>
        </tr>
    </table>

    <div class="ceot-note" style="margin-top:26px; font-size:8px">
        Este documento forma parte del registro clínico del paciente y debe conservarse de acuerdo con las políticas de confidencialidad aplicables.
    </div>

</body>
</html>
