<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Movimientos</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        .header { width: 100%; border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; color: #2563eb; }
        .header p { margin: 2px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f4f4f5; font-weight: bold; color: #444; }
        .sub { font-size: 9px; color: #666; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 2px 4px; border-radius: 4px; color: #fff; font-size: 9px; }
        .bg-green { background-color: #10b981; }
        .bg-red { background-color: #f43f5e; }
        .bg-gray { background-color: #64748b; }
        .total-box { float: right; width: 300px; }
        .total-row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Movimientos de Inventario</h1>
        <p>Generado el: {{ date('d/m/Y H:i') }}</p>
        <p>Usuario: {{ auth()->user()->name }}</p>
        @if($r->from || $r->to)
            <p>Rango: {{ $r->from ?? 'inicio' }} al {{ $r->to ?? 'fin' }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th width="12%">Fecha</th>
                <th>Producto / SKU</th>
                <th width="12%">Tipo</th>
                <th width="10%" class="text-right">Cant.</th>
                <th width="10%" class="text-right">Costo</th>
                <th width="15%">Lote / Venc.</th>
                <th width="10%">Ubicación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movs as $m)
                <tr>
                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        {{ $m->product->name ?? 'Eliminado' }}
                        <div class="sub">{{ $m->product->sku ?? '' }}</div>
                    </td>
                    <td>
                        @if($m->type == 'in') <span class="badge bg-green">Entrada</span>
                        @elseif($m->type == 'out') <span class="badge bg-red">Salida</span>
                        @else <span class="badge bg-gray">{{ ucfirst($m->type) }}</span> @endif
                    </td>
                    <td class="text-right">
                        {{ number_format($m->qty, 0) }}
                        <div class="sub">{{ $m->product->unit ?? '' }}</div>
                    </td>
                    <td class="text-right">
                        @if($m->unit_cost) {{ number_format($m->unit_cost, 2) }} @else - @endif
                    </td>
                    <td>
                        {{ $m->lot }}
                        @if($m->expires_at)
                            <div class="sub">{{ $m->expires_at->format('d/m/Y') }}</div>
                        @endif
                    </td>
                    <td>{{ $m->location->name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        <div class="total-row">
            <span class="label">Total Movimientos:</span>
            <span>{{ $movs->count() }}</span>
        </div>
        <div class="total-row">
            <span class="label">Total Entradas:</span>
            <span>{{ $movs->where('type', 'in')->count() }}</span>
        </div>
        <div class="total-row">
            <span class="label">Total Salidas:</span>
            <span>{{ $movs->where('type', 'out')->count() }}</span>
        </div>
    </div>
</body>
</html>
