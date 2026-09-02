<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class TreatmentPlan extends Model
{
    use Auditable;
    protected $table = 'treatment_plans';

    protected $fillable = [
        'patient_id',
        'title',
        'estimate_total',
        'total_sessions',
        'completed_sessions',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'estimate_total'     => 'decimal:2',
        'total_sessions'     => 'integer',
        'completed_sessions' => 'integer',
        'approved_at'        => 'datetime',
    ];

    // Relaciones
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Alias items/treatments (ya lo tenías)
    public function items()
    {
        return $this->hasMany(Treatment::class);
    }

    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }

    // Citas generadas a partir del plan
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'treatment_plan_id');
    }

    // Facturas vinculadas al plan
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'treatment_plan_id');
    }

    // La factura más reciente del plan
    public function getInvoiceLatestAttribute()
    {
        return $this->invoices()->latest()->first();
    }

    // Total pagado en todas las facturas del plan
    public function getPaidAmountAttribute(): float
    {
        return (float) Payment::whereIn(
            'invoice_id',
            $this->invoices()->pluck('id')
        )->sum('amount');
    }

    // Saldo pendiente
    public function getBalanceAttribute(): float
    {
        return round((float) $this->estimate_total - $this->paid_amount, 2);
    }

    public function updateEstimateTotal(): void
    {
        $this->update([
            'estimate_total' => $this->treatments()->sum('price'),
        ]);
    }

    /**
     * Recalcula completed_sessions contando tratamientos con status 'done'.
     */
    public function refreshProgress(): void
    {
        $this->update([
            'completed_sessions' => $this->treatments()->where('status', 'done')->count(),
        ]);
    }
}
