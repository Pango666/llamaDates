<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Invoice extends Model
{
    protected $fillable = [
        'number', 'patient_id', 'treatment_plan_id', 'total', 'status', 'issued_at', 'notes'
    ];

    protected $casts = [
        'discount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    

    public function patient(): BelongsTo  { return $this->belongsTo(Patient::class); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
    public function treatmentPlan(): BelongsTo { return $this->belongsTo(TreatmentPlan::class); }
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    /** Subtotal = suma de items total */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum('total');
    }

    /** Total con descuento e impuesto */
    public function getGrandTotalAttribute(): float
    {
        $sub = $this->subtotal;
        $afterDiscount = max(0, $sub - (float)$this->discount);
        $tax = $afterDiscount * ((float)$this->tax_percent / 100);
        return round($afterDiscount + $tax, 2);
    }

    /** Pagado */
    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments->sum('amount');
    }

    /** Saldo */
    public function getBalanceAttribute(): float
    {
        return round($this->grand_total - $this->paid_amount, 2);
    }

    public function scopeOpen($q) { return $q->whereIn('status',['issued','draft']); }

    public static function nextNumber(): string
    {
        $prefix = now()->format('Y');
        $last = static::where('number', 'like', $prefix.'-%')
            ->orderByDesc('number')
            ->value('number');

        $seq = 1;
        if ($last && preg_match('/^'.$prefix.'-(\d{4})$/', $last, $m)) {
            $seq = (int)$m[1] + 1;
        }
        return sprintf('%s-%04d', $prefix, $seq);
    }
}
