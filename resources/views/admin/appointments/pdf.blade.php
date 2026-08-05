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
        | <strong>Fecha:</strong> {{ $filters['date'] ? \Carbon\Carbon::parse($filters['date'])->format('d/m/Y') : 'Todas' }}
        @if($filters['dentist_id'])
            | <strong>Odontólogo:</strong> {{ $dentists->firstWhere('id', $filters['dentist_id'])->name ?? 'ID '.$filters['dentist_id'] }}
        @endif
        @if($filters['status']) | <strong>Estado:</strong> {{ str_replace('_', ' ', $filters['status']) }} @endif
        @if($filters['q'] ?? null) | <strong>Búsqueda:</strong> “{{ $filters['q'] }}” @endif
    </div>

    <div class="ceot-section">Resumen de actividad</div>
    <table class="ceot-stats">
        <tr>
            <td><span class="ceot-stat-value">{{ number_format($statusCounts['reserved']) }}</span><span class="ceot-stat-label">Reservadas</span></td>
            <td><span class="ceot-stat-value">{{ number_format($statusCounts['confirmed']) }}</span><span class="ceot-stat-label">Confirmadas</span></td>
            <td><span class="ceot-stat-value">{{ number_format($statusCounts['in_service']) }}</span><span class="ceot-stat-label">En atención</span></td>
            <td><span class="ceot-stat-value">{{ number_format($statusCounts['done']) }}</span><span class="ceot-stat-label">Atendidas</span></td>
            <td><span class="ceot-stat-value">{{ number_format($statusCounts['no_show']) }}</span><span class="ceot-stat-label">No asistió</span></td>
            <td><span class="ceot-stat-value">{{ number_format($statusCounts['canceled']) }}</span><span class="ceot-stat-label">Canceladas</span></td>
        </tr>
    </table>

    <div class="ceot-section">Odontólogos más solicitados</div>
    <table class="ceot-table ranking">
        <thead><tr><th style="width:10%">#</th><th>Odontólogo</th><th class="text-center" style="width:24%">Citas</th></tr></thead>
        <tbody>
            @forelse($topDentists as $index => $dentist)
                <tr><td>{{ $index + 1 }}</td><td>{{ $dentist->name }}</td><td class="text-center font-bold">{{ $dentist->total }}</td></tr>
            @empty
                <tr><td colspan="3" class="text-center muted">Sin datos para los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="ceot-section">Servicios solicitados</div>
    <table class="ceot-table">
        <thead><tr><th>Servicio</th><th class="text-center" style="width:16%">Total</th><th class="text-center" style="width:16%">Participación</th></tr></thead>
        <tbody>
            @forelse($serviceStats as $service)
                <tr>
                    <td>{{ $service->name }}</td>
                    <td class="text-center">{{ $service->total }}</td>
                    <td class="text-center">{{ $totalAppointments > 0 ? number_format(($service->total / $totalAppointments) * 100, 1) : 0 }}%</td>
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
