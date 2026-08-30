<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Movimientos de inventario</title>
    @include('admin.pdf.partials.styles')
</head>
<body>
    @php
        $entries = $movs->where('type', 'in')->count();
        $outputs = $movs->where('type', 'out')->count();
        $adjustments = $movs->count() - $entries - $outputs;
    @endphp

    @include('admin.pdf.partials.footer', ['label' => 'Trazabilidad de inventario'])

    @include('admin.pdf.partials.header', [
        'kicker' => 'Control de inventario',
        'title' => 'Movimientos de inventario',
        'subtitle' => $movs->count().' movimientos incluidos',
    ])

    <div class="ceot-meta">
        <strong>Generado por:</strong> {{ auth()->user()->name }}
        | <strong>Fecha/Hora de generación:</strong> {{ now()->format('d/m/Y H:i') }}
        @if($r->from || $r->to)
            | <strong>Periodo:</strong> {{ $r->from ?? 'Inicio' }} al {{ $r->to ?? 'Fin' }}
        @else
            | <strong>Periodo:</strong> Histórico
        @endif
    </div>

    <div class="ceot-section">Resumen de movimientos</div>
    <table class="ceot-stats">
        <tr>
            <td><span class="ceot-stat-value">{{ $movs->count() }}</span><span class="ceot-stat-label">Movimientos</span></td>
            <td><span class="ceot-stat-value" style="color:#08785c">{{ $entries }}</span><span class="ceot-stat-label">Entradas</span></td>
            <td><span class="ceot-stat-value" style="color:#a72a42">{{ $outputs }}</span><span class="ceot-stat-label">Salidas</span></td>
            <td><span class="ceot-stat-value" style="color:#536578">{{ $adjustments }}</span><span class="ceot-stat-label">Ajustes</span></td>
        </tr>
    </table>

    <div class="ceot-section">Trazabilidad</div>
    <table class="ceot-table">
        <thead>
            <tr><th style="width:12%">Fecha</th><th>Producto / SKU</th><th style="width:10%">Tipo</th><th class="text-right" style="width:9%">Cant.</th><th class="text-right" style="width:10%">Costo</th><th style="width:14%">Lote / Venc.</th><th style="width:11%">Ubicación</th><th style="width:11%">Usuario</th></tr>
        </thead>
        <tbody>
            @forelse($movs as $movement)
                <tr>
                    <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                    <td><span class="font-bold">{{ $movement->product->name ?? 'Producto eliminado' }}</span><br><span class="small muted">{{ $movement->product->sku ?? 'Sin SKU' }}</span></td>
                    <td>
                        @if($movement->type === 'in')<span class="ceot-badge badge-green">Entrada</span>
                        @elseif($movement->type === 'out')<span class="ceot-badge badge-red">Salida</span>
                        @else<span class="ceot-badge badge-slate">{{ ucfirst($movement->type) }}</span>@endif
                    </td>
                    <td class="text-right">{{ number_format($movement->qty, 0) }}<br><span class="small muted">{{ $movement->product->unit ?? '' }}</span></td>
                    <td class="text-right">{{ $movement->unit_cost ? number_format($movement->unit_cost, 2) : '-' }}</td>
                    <td>{{ $movement->lot ?: '-' }}@if($movement->expires_at)<br><span class="small muted">Vence {{ $movement->expires_at->format('d/m/Y') }}</span>@endif</td>
                    <td>{{ $movement->location->name ?? '-' }}</td>
                    <td>{{ $movement->user->name ?? 'Sistema' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center muted">No se encontraron movimientos con los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
