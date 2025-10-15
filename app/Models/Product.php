<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'presentation',
        'unit',
        'brand',
        'supplier_id',
        'min_stock',
        'sell_price',
        'is_active'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /** Stock total on-hand (todas las locations) */
    public function stockOnHand(): float
    {
        return (float) InventoryMovement::where('product_id', $this->id)->sum('qty');
    }

    /** Stock por location opcional */
    public function stockAt(?int $locationId): float
    {
        if (!$locationId) return $this->stockOnHand();
        return (float) InventoryMovement::where('product_id', $this->id)
            ->where('location_id', $locationId)->sum('qty');
    }
}
