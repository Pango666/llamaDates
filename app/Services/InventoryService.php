<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Devuelve stock on-hand por producto/location opcional y lot opcional.
     */
    public function onHand(int $productId, ?int $locationId = null, ?string $lot = null): float
    {
        $q = InventoryMovement::where('product_id',$productId);
        if ($locationId) $q->where('location_id',$locationId);
        if ($lot !== null && $lot !== '') $q->where('lot',$lot);
        return (float) $q->sum('qty'); // qty: in=+, out=-, adjust/transfer según signo
    }

    /**
     * Costo promedio ponderado actual (por location opcional).
     * Si no hay entradas con costo, devuelve null.
     */
    public function avgCost(int $productId, ?int $locationId = null): ?float
    {
        $q = InventoryMovement::where('product_id',$productId)->whereNotNull('unit_cost');
        if ($locationId) $q->where('location_id',$locationId);

        // promedio ponderado = sum(qty_pos * cost) / sum(qty_pos)
        $ins = (clone $q)->where('qty','>',0)->get(['qty','unit_cost']);
        $sumQty = 0; $sumVal = 0;
        foreach ($ins as $m) { $sumQty += (float)$m->qty; $sumVal += (float)$m->qty * (float)$m->unit_cost; }
        if ($sumQty <= 0) return null;
        return round($sumVal / $sumQty, 4);
    }

    /**
     * Registrar entrada/salida/ajuste/traslado (qty positivo o negativo).
     */
    public function move(array $data): InventoryMovement
    {
        // Validación básica
        foreach (['product_id','location_id','type','qty'] as $k) {
            if (!isset($data[$k])) throw new InvalidArgumentException("Falta $k");
        }
        if (!in_array($data['type'], ['in','out','adjust','transfer'], true)) {
            throw new InvalidArgumentException('Tipo inválido');
        }
        if ((float)$data['qty'] === 0.0) {
            throw new InvalidArgumentException('Cantidad no puede ser 0');
        }

        return DB::transaction(function() use ($data) {
            // Si es salida, valida stock suficiente (por lot si se indicó)
            if (in_array($data['type'], ['out','transfer']) && (float)$data['qty'] > 0) {
                // forzamos salidas negativas internamente
                $data['qty'] = -abs((float)$data['qty']);
            }

            if ($data['type'] === 'out') {
                $onHand = $this->onHand($data['product_id'], $data['location_id'], $data['lot'] ?? null);
                if ($onHand + $data['qty'] < -0.0001) { // data['qty'] es negativo
                    throw new InvalidArgumentException('Stock insuficiente');
                }
            }

            // Graba
            $data['user_id'] = $data['user_id'] ?? optional(auth()->user())->id;
            return InventoryMovement::create($data);
        });
    }
}
