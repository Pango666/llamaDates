<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'auditable_label',
        'old_values',
        'new_values',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    // ─── Relationships ───

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Helpers ───

    /**
     * Human-readable model name.
     */
    public function getModelNameAttribute(): string
    {
        $map = [
            'App\\Models\\Patient'       => 'Paciente',
            'App\\Models\\Appointment'    => 'Cita',
            'App\\Models\\Invoice'        => 'Factura',
            'App\\Models\\Product'        => 'Producto',
            'App\\Models\\Service'        => 'Servicio',
            'App\\Models\\Dentist'        => 'Odontólogo',
            'App\\Models\\User'           => 'Usuario',
            'App\\Models\\TreatmentPlan'  => 'Plan de Tratamiento',
        ];

        return $map[$this->auditable_type] ?? class_basename($this->auditable_type);
    }

    /**
     * Human-readable action name.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created'  => 'Creó',
            'updated'  => 'Modificó',
            'deleted'  => 'Eliminó',
            'toggled'  => 'Activó/Desactivó',
            default    => ucfirst($this->action),
        };
    }

    /**
     * Get changed fields (diff between old and new).
     */
    public function getChangedFieldsAttribute(): array
    {
        if ($this->action !== 'updated' || !$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];
        foreach ($this->new_values as $key => $newVal) {
            $oldVal = $this->old_values[$key] ?? null;
            if ($oldVal !== $newVal) {
                $changes[$key] = [
                    'old' => $oldVal,
                    'new' => $newVal,
                ];
            }
        }
        return $changes;
    }
}
