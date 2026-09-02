<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de citas</title>
    @include('admin.pdf.partials.styles')
    <style>
        .ranking { width: 62%; }
        .patient-phone { margin-top: 2px; color: #75899a; font-size: 8px; }
    </style>
</head>
<body>
    @include('admin.pdf.partials.footer', ['label' => 'Reporte operativo confidencial'])
    @include('admin.pdf.partials.header', [
        'kicker' => 'Operación clínica',
        'title' => 'Reporte de citas',
        'subtitle' => number_format($totalAppointments).' registros incluidos',
    ])

    <div class="ceot-meta">
        <strong>Generado por:</strong> {{ auth()->user()->name }}
        | <strong>Fecha/Hora de generación:</strong> {{ now()->format('d/m/Y H:i') }}
        | <strong>Fecha filtro:</strong> {{ $filters['date_start'] ? \Carbon\Carbon::parse($filters['date_start'])->format('d/m/Y') . ($filters['date_end'] && $filters['date_end'] != $filters['date_start'] ? ' al ' . \Carbon\Carbon::parse($filters['date_end'])->format('d/m/Y') : '') : 'Todas' }}
        @if($filters['dentist_id'])
            | <strong>Odontólogo:</strong> {{ $dentists->firstWhere('id', $filters['dentist_id'])->name ?? 'ID '.$filters['dentist_id'] }}
        @endif
        @if($filters['status']) | <strong>Estado:</strong> {{ str_replace('_', ' ', $filters['status']) }} @endif
        @if($filters['q'] ?? null) | <strong>Búsqueda:</strong> "{{ $filters['q'] }}" @endif
    </div>

    @php
        $efectivas = $statusCounts['done'] + $statusCounts['in_service'];
        $enEspera  = $statusCounts['confirmed'] + $statusCounts['reserved'];
        $perdidas  = $statusCounts['no_show'] + $statusCounts['canceled'];
        $totalCitas = $efectivas + $enEspera + $perdidas;
    @endphp
    <div class="ceot-section">Resumen de actividad</div>
    <div style="margin-bottom: 14px; padding: 10px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
            <tr>
                <td style="padding: 8px 10px; width: 33%; text-align: center; border-right: 1px solid #e2e8f0;">
                    <div style="font-size: 20px; font-weight: 700; color: #059669;">{{ $efectivas }}</div>
                    <div style="font-size: 9px; color: #065f46; font-weight: 600; text-transform: uppercase; letter-spacing: .5px;">Efectivas</div>
                    <div style="font-size: 8px; color: #6b7280; margin-top: 2px;">Atendidas ({{ $statusCounts['done'] }}) + En Atención ({{ $statusCounts['in_service'] }})</div>
                </td>
                <td style="padding: 8px 10px; width: 33%; text-align: center; border-right: 1px solid #e2e8f0;">
                    <div style="font-size: 20px; font-weight: 700; color: #d97706;">{{ $enEspera }}</div>
                    <div style="font-size: 9px; color: #92400e; font-weight: 600; text-transform: uppercase; letter-spacing: .5px;">En Espera</div>
                    <div style="font-size: 8px; color: #6b7280; margin-top: 2px;">Confirmadas ({{ $statusCounts['confirmed'] }}) + Reservadas ({{ $statusCounts['reserved'] }})</div>
                </td>
                <td style="padding: 8px 10px; width: 33%; text-align: center;">
                    <div style="font-size: 20px; font-weight: 700; color: #dc2626;">{{ $perdidas }}</div>
                    <div style="font-size: 9px; color: #991b1b; font-weight: 600; text-transform: uppercase; letter-spacing: .5px;">Perdidas</div>
                    <div style="font-size: 8px; color: #6b7280; margin-top: 2px;">No Asistió ({{ $statusCounts['no_show'] }}) + Canceladas ({{ $statusCounts['canceled'] }})</div>
                </td>
            </tr>
        </table>
        @if($totalCitas > 0)
        <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 9px; color: #475569;">
            <strong>Tasa de efectividad:</strong> {{ number_format(($efectivas / $totalCitas) * 100, 1) }}%
            &nbsp;|&nbsp; <strong>Total de citas:</strong> {{ $totalCitas }}
        </div>
        @endif
    </div>

    <div class="ceot-section">Rendimiento por Odontólogo</div>
    <table class="ceot-table ranking">
        <thead>
            <tr>
                <th style="width:8%">#</th>
                <th>Odontólogo</th>
                <th class="text-center" style="width:18%">Agendadas</th>
                <th class="text-center" style="width:18%">Atendidas</th>
                <th class="text-center" style="width:18%">Efectividad</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topDentists as $index => $dentist)
                @php $pct = $dentist->total > 0 ? ($dentist->atendidas / $dentist->total) * 100 : 0; @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $dentist->name }}</td>
                    <td class="text-center">{{ $dentist->total }}</td>
                    <td class="text-center font-bold" style="color:#059669">{{ $dentist->atendidas }}</td>
                    <td class="text-center" style="color: {{ $pct >= 80 ? '#059669' : ($pct >= 50 ? '#d97706' : '#dc2626') }}; font-weight: 700;">{{ number_format($pct, 0) }}%</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center muted">Sin datos para los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="ceot-section">Servicios</div>
    <table class="ceot-table">
        <thead>
            <tr>
                <th>Servicio</th>
                <th class="text-center" style="width:20%">Citas del Servicio</th>
                <th class="text-center" style="width:22%">Sesiones Realizadas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($serviceStats as $service)
                <tr>
                    <td>{{ $service->name }}</td>
                    <td class="text-center">{{ $service->total }}</td>
                    <td class="text-center font-bold" style="color:#059669">{{ $service->sesiones }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center muted">Sin servicios para mostrar.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($appointments->count() > 0)
        @php
            $statusLabels = ['reserved'=>'Reservada', 'confirmed'=>'Confirmada', 'in_service'=>'En atención', 'done'=>'Atendida', 'no_show'=>'No asistió', 'canceled'=>'Cancelada'];
            $statusClasses = ['reserved'=>'badge-amber', 'confirmed'=>'badge-blue', 'in_service'=>'badge-cyan', 'done'=>'badge-green', 'no_show'=>'badge-red', 'canceled'=>'badge-slate'];
        @endphp
        <div class="ceot-section">Detalle de citas</div>
        <table class="ceot-table">
            <thead>
                <tr><th style="width:11%">Fecha</th><th style="width:8%">Hora</th><th>Paciente</th><th>Servicio</th><th>Odontólogo</th><th style="width:12%">Estado</th></tr>
            </thead>
            <tbody>
                @foreach($appointments as $appointment)
                    @php $status = in_array($appointment->status, ['no_show', 'non-attendance']) ? 'no_show' : $appointment->status; @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}</td>
                        <td>
                            <span class="font-bold">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</span>
                            @if($appointment->patient->phone)<div class="patient-phone">{{ $appointment->patient->phone }}</div>@endif
                        </td>
                        <td>{{ $appointment->service->name }}</td>
                        <td>{{ $appointment->dentist->name }}</td>
                        <td><span class="ceot-badge {{ $statusClasses[$status] ?? 'badge-slate' }}">{{ $statusLabels[$status] ?? $status }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="ceot-note text-center">No se encontraron citas con los filtros seleccionados.</div>
    @endif

</body>
</html>
