<?php

use App\Models\Product;
use App\Models\InventoryMovement;

// Buscamos el producto del screenshot (MED-0004 o Clorhexidina)
$p = Product::where('sku', 'MED-0004')->orWhere('name', 'like', '%Clorhexidina%')->first();

if(!$p) {
    echo "Producto no encontrado.\n";
    exit;
}

echo "Producto: {$p->name} (ID: {$p->id})\n";
echo "Global Stock (DB cache): {$p->stock}\n";

$movs = InventoryMovement::where('product_id', $p->id)->get();

echo "\n--- MOVIMIENTOS ---\n";
foreach($movs as $m) {
    echo "ID: {$m->id} | Tipo: {$m->type} | Qty: {$m->qty} | Lote: '{$m->lot}' | Exp: {$m->expires_at}\n";
}

echo "\n--- BALANCE POR LOTE ---\n";
$lots = $movs->groupBy('lot');
foreach($lots as $lotName => $group) {
    $balance = $group->sum(function($m){
        return ($m->type == 'out') ? -$m->qty : $m->qty;
    });
    $lotNameDisplay = $lotName === '' ? '(VACIO)' : ($lotName === null ? '(NULL)' : $lotName);
    echo "Lote: '{$lotNameDisplay}' => Balance: {$balance}\n";
}
