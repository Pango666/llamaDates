<?php

namespace App\Services;

use App\Models\Product;

class PricingService
{
    public function __construct(private InventoryService $stock) {}

    /**
     * Precio de venta sugerido:
     * - Si product.sell_price: úsalo.
     * - Si no, usa costo promedio × (1 + markup% configurado).
     */
    public function sellingPrice(Product $product, ?int $locationId = null): float
    {
        if ($product->sell_price !== null) {
            return (float) $product->sell_price;
        }
        $markupPct = (float) config('inventory.default_markup_pct', 25); // 25% por defecto
        $avg = $this->stock->avgCost($product->id, $locationId) ?? 0;
        $price = $avg * (1 + $markupPct/100);
        return round($price, 2);
    }
}
