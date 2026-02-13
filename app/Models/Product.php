<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class Product extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'product_category_id',
        'presentation_unit_id',
        'presentation_detail',
        'concentration_value',
        'concentration_unit_id',
        'unit',
        'brand',
        'supplier_id',
        'stock',
        'min_stock',
        'is_active',
    ];

    protected $casts = [
        'concentration_value' => 'decimal:3',
        'stock'              => 'integer',
        'min_stock'          => 'integer',
        'is_active'          => 'boolean',
    ];

    // Relaciones
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function presentationUnit()
    {
        return $this->belongsTo(ProductPresentationUnit::class, 'presentation_unit_id');
    }

    public function concentrationUnit()
    {
        return $this->belongsTo(MeasurementUnit::class, 'concentration_unit_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function appointmentSupplies()
    {
        return $this->hasMany(AppointmentSupply::class);
    }

    // Accessors útiles 

    // label de concentración: "500 mg", "2 %", etc.
    public function getConcentrationLabelAttribute(): ?string
    {
        if (is_null($this->concentration_value) || !$this->concentrationUnit) {
            return null;
        }

        $value = rtrim(rtrim(number_format($this->concentration_value, 3, '.', ''), '0'), '.');

        return $value . ' ' . $this->concentrationUnit->symbol;
    }

    // label de presentación: "Carpule · Caja x 50 carpules", "Tableta · Caja x 20 tabletas"
    public function getPresentationLabelAttribute(): ?string
    {
        $parts = [];

        if ($this->presentationUnit) {
            $parts[] = $this->presentationUnit->name;
        }

        if ($this->presentation_detail) {
            $parts[] = $this->presentation_detail;
        }

        return count($parts) ? implode(' · ', $parts) : null;
    }

    // Scope productos activos
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope stock bajo
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }
}
