<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Citas</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        h1 { margin: 0; font-size: 18px; color: #1e293b; }
        .meta { font-size: 10px; color: #64748b; margin-top: 5px; }
        
        .section-title { font-size: 14px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; color: #334155; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        
        .stats-grid { display: block; width: 100%; margin-bottom: 20px; }
        .stat-box { display: inline-block; width: 15%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px; text-align: center; margin-right: 1%; vertical-align: top; }
        .stat-value { font-size: 16px; font-weight: bold; color: #0f172a; display: block; }
        .stat-label { font-size: 9px; color: #64748b; text-transform: uppercase; margin-top: 4px; display: block; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; color: #475569; }
        
        .badge { display: inline-block; padding: 2px 6px; border-radius: 9999px; font-size: 9px; font-weight: bold; }
        .badge-reserved { background: #fef3c7; color: #b45309; }
        .badge-confirmed { background: #dbeafe; color: #1d4ed8; }
        .badge-in_service { background: #ffedd5; color: #c2410c; }
        .badge-done { background: #d1fae5; color: #047857; }
        .badge-canceled { background: #f1f5f9; color: #64748b; }
        .badge-no_show { background: #ffe4e6; color: #be123c; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 9px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <header>
        <h1>Reporte de Citas</h1>
        <div class="meta">
            Generado el: {{ now()->format('d/m/Y H:i') }} | Por: {{ auth()->user()->name }}
            <br>
            Filtros: 
            @if($filters['date']) Fecha: <b>{{ $filters['date'] }}</b> @else Todas las fechas @endif
            @if($filters['dentist_id']) | Odontólogo: <b>{{ $dentists->firstWhere('id', $filters['dentist_id'])->name ?? 'ID '.$filters['dentist_id'] }}</b> @endif
            @if($filters['status']) | Estado: <b>{{ $filters['status'] }}</b> @endif
        </div>
    </header>

    {{-- Resumen de Estados --}}
    <div class="section-title">Resumen de Actividad</div>
    <div class="stats-grid">
        <div class="stat-box">
            <span class="stat-value">{{ number_format($statusCounts['reserved']) }}</span>
            <span class="stat-label">Reservadas</span>
        </div>
        <div class="stat-box">
            <span class="stat-value">{{ number_format($statusCounts['confirmed']) }}</span>
            <span class="stat-label">Confirmadas</span>
        </div>
        <div class="stat-box">
            <span class="stat-value">{{ number_format($statusCounts['done']) }}</span>
            <span class="stat-label">Atendidas</span>
        </div>
        <div class="stat-box">
            <span class="stat-value">{{ number_format($statusCounts['in_service']) }}</span>
            <span class="stat-label">En Atención</span>
        </div>
        <div class="stat-box">
            <span class="stat-value">{{ number_format($statusCounts['no_show']) }}</span>
            <span class="stat-label">No Asistió</span>
        </div>
        <div class="stat-box">
            <span class="stat-value">{{ number_format($statusCounts['canceled']) }}</span>
            <span class="stat-label">Canceladas</span>
        </div>
    </div>

    {{-- Top Odontólogos --}}
    <div class="section-title">Top Odontólogos Solicitados</div>
    <table style="width: 60%;">
        <thead>
            <tr>
                <th style="width: 10%;">#</th>
                <th>Odontólogo</th>
                <th style="width: 20%; text-align: center;">Citas Totales</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topDentists as $index => $d)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $d->name }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $d->total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Servicios Más Solicitados --}}
    <div class="section-title">Servicios Solicitados</div>
    <table>
        <thead>
            <tr>
                <th>Servicio</th>
                <th style="width: 15%; text-align: center;">Total Citas</th>
                <th style="width: 15%; text-align: center;">% del Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($serviceStats as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td style="text-align: center;">{{ $s->total }}</td>
                    <td style="text-align: center;">
                        {{ $totalAppointments > 0 ? number_format(($s->total / $totalAppointments) * 100, 1) : 0 }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Listado Detallado (Limitado a 500 para no explotar PDF) --}}
    @if($appointments->count() > 0)
        <div class="section-title">Detalle de Citas ({{ $appointments->count() }} mostradas)</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Fecha</th>
                    <th style="width: 10%;">Hora</th>
                    <th>Paciente</th>
                    <th>Servicio</th>
                    <th>Odontólogo</th>
                    <th style="width: 12%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($appointments as $a)
                    @php
                       $st = in_array($a->status, ['no_show','non-attendance']) ? 'no_show' : $a->status;
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($a->date)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($a->start_time)->format('H:i') }}</td>
                        <td>
                            {{ $a->patient->first_name }} {{ $a->patient->last_name }}
                            @if($a->patient->phone)<br><span style="color:#666; font-size:9px;">{{ $a->patient->phone }}</span>@endif
                        </td>
                        <td>{{ $a->service->name }}</td>
                        <td>{{ $a->dentist->name }}</td>
                        <td>
                            @php
                                $labels = [
                                    'reserved'   => 'Reservado',
                                    'confirmed'  => 'Confirmado',
                                    'in_service' => 'En Atención',
                                    'done'       => 'Atendido',
                                    'no_show'    => 'No Asistió',
                                    'canceled'   => 'Cancelado',
                                ];
                            @endphp
                            <span class="badge badge-{{ $st }}">{{ strtoupper($labels[$st] ?? $st) }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        DentalCare System - Reporte generado por {{ auth()->user()->email }} - Página 1
    </div>
</body>
</html>
