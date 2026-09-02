<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Plan de tratamiento #{{ $plan->id }}</title>
    @include('admin.pdf.partials.styles')
    <style>
        .no-print { margin-bottom: 12px; text-align: right; }
        .print-button { padding: 7px 12px; border: 0; border-radius: 6px; background: #075aa5; color: #fff; cursor: pointer; }
        .acceptance { width: 100%; margin-top: 42px; border-collapse: separate; border-spacing: 24px 0; page-break-inside: avoid; }
        .acceptance td { width: 50%; padding-top: 28px; border: 0; border-top: 1px solid #536578; text-align: center; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    @php
        $planLabels = ['draft'=>'Borrador', 'approved'=>'Aprobado', 'in_progress'=>'En progreso', 'completed'=>'Completado', 'canceled'=>'Cancelado'];
        $planClasses = ['draft'=>'badge-slate', 'approved'=>'badge-green', 'in_progress'=>'badge-blue', 'completed'=>'badge-cyan', 'canceled'=>'badge-red'];
        $treatmentLabels = ['pending'=>'Pendiente', 'scheduled'=>'Programado', 'in_progress'=>'En progreso', 'completed'=>'Completado', 'canceled'=>'Cancelado'];
    @endphp

    @include('admin.pdf.partials.footer', ['label' => 'Plan de tratamiento confidencial'])

    @unless($isPdf ?? false)
        <div class="no-print"><button class="print-button" onclick="window.print()">Imprimir documento</button></div>
    @endunless
    @include('admin.pdf.partials.header', [
        'kicker' => 'Propuesta clínica',
        'title' => 'Plan de tratamiento #'.$plan->id,
        'subtitle' => $plan->title,
    ])

    <div class="ceot-meta">
        <strong>Paciente:</strong> {{ $plan->patient->last_name }}, {{ $plan->patient->first_name }}
        @if($plan->patient->ci) | <strong>CI:</strong> {{ $plan->patient->ci }} @endif
        | <strong>Estado:</strong> <span class="ceot-badge {{ $planClasses[$plan->status] ?? 'badge-slate' }}">{{ $planLabels[$plan->status] ?? str_replace('_', ' ', $plan->status) }}</span>
        @if($plan->total_sessions > 0)
        | <strong>Progreso:</strong> {{ $plan->completed_sessions }} / {{ $plan->total_sessions }} sesiones completadas
        @endif
        @if($plan->approved_at) | <strong>Aprobado:</strong> {{ $plan->approved_at->format('d/m/Y H:i') }} por {{ $plan->approver?->name }} @endif
        | <strong>Fecha/Hora de generación:</strong> {{ now()->format('d/m/Y H:i') }}
    </div>

    <div class="ceot-section">Procedimientos propuestos</div>
    <table class="ceot-table">
        <thead><tr><th>Servicio</th><th style="width:9%">Pieza</th><th style="width:9%">Sup.</th><th class="text-right" style="width:14%">Precio (Bs)</th><th style="width:13%">Estado</th><th>Notas</th></tr></thead>
        <tbody>
            @forelse($plan->treatments as $treatment)
                <tr>
                    <td class="font-bold">{{ $treatment->service?->name ?? '-' }}</td>
                    <td>{{ $treatment->tooth_code ?: '-' }}</td>
                    <td>{{ $treatment->surface ?: '-' }}</td>
                    <td class="text-right">{{ number_format($treatment->price, 2) }}</td>
                    <td>{{ $treatmentLabels[$treatment->status] ?? str_replace('_', ' ', $treatment->status) }}</td>
                    <td>{{ $treatment->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center muted">El plan todavía no contiene procedimientos.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table style="width:100%; border-collapse:collapse; page-break-inside:avoid">
        <tr>
            <td style="width:56%; border:0; padding:0 18px 0 0; vertical-align:top">
                <div class="ceot-note">
                    <strong>Información importante</strong><br>
                    El valor es una estimación basada en los procedimientos descritos. Puede variar si durante la atención se identifican necesidades clínicas adicionales, las cuales deberán informarse al paciente.
                </div>
            </td>
            <td style="width:44%; border:0; padding:0; vertical-align:top">
                <div class="ceot-total-card">
                    <table class="ceot-total-table">
                        <tr><td>Costo estimado</td><td class="text-right">Bs {{ number_format($plan->estimate_total, 2) }}</td></tr>
                        <tr><td>Monto pagado</td><td class="text-right" style="color: #059669;">Bs {{ number_format($plan->paid_amount, 2) }}</td></tr>
                        <tr class="ceot-total-row"><td>Saldo pendiente</td><td class="text-right" style="color: #dc2626;">Bs {{ number_format($plan->balance, 2) }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="acceptance">
        <tr>
            <td><span class="font-bold">Aceptación del paciente</span><br><span class="muted">Firma y fecha</span></td>
            <td><span class="font-bold">Profesional responsable</span><br><span class="muted">Firma y sello</span></td>
        </tr>
    </table>

</body>
</html>
