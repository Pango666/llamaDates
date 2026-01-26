<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'location_id',
        'type',
        'qty',
        'unit_cost',
        'purchase_invoice_number',
        'lot',
        'expires_at',
        'appointment_id',
        'user_id',
        'note',
    ];

    protected $casts = [
        'qty'        => 'integer',
        'unit_cost'  => 'decimal:4',
        'expires_at' => 'date',
    ];

    public const TYPE_IN       = 'in';
    public const TYPE_OUT      = 'out';
    public const TYPE_ADJUST   = 'adjust';
    public const TYPE_TRANSFER = 'transfer';

    public static function types(): array
    {
        return [
            self::TYPE_IN,
            self::TYPE_OUT,
            self::TYPE_ADJUST,
            self::TYPE_TRANSFER,
        ];
    }

    // Relaciones
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
