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
        if ($this->action === 'updated') {
            if ($this->auditable_type === 'App\\Models\\Invoice') {
                $changes = $this->changed_fields;
                if (isset($changes['status'])) {
                    if ($changes['status']['new'] === 'paid') return 'Cobró la factura';
                    if ($changes['status']['new'] === 'canceled') return 'Anuló la factura';
                }
            }
            if ($this->auditable_type === 'App\\Models\\Appointment') {
                $changes = $this->changed_fields;
                if (isset($changes['status'])) {
                    if ($changes['status']['new'] === 'canceled') return 'Canceló la cita';
                    if ($changes['status']['new'] === 'confirmed') return 'Confirmó la cita';
                    if ($changes['status']['new'] === 'in_service') return 'Inició la atención';
                    if ($changes['status']['new'] === 'done') return 'Finalizó la atención';
                    if ($changes['status']['new'] === 'no_show') return 'Marcó como No Asistió';
                }
            }
            return 'Modificó';
        }

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

    /**
     * Get translated and filtered changed fields for display.
     */
    public function getFormattedChangesAttribute(): array
    {
        $changes = $this->changed_fields;
        $formatted = [];

        $fieldTranslations = [
            'status' => 'Estado',
            'paid_at' => 'Fecha de Pago',
            'is_active' => 'Activo',
            'notes' => 'Notas',
            'discount' => 'Descuento',
            'tax_percent' => 'Impuesto (%)',
            'date' => 'Fecha',
            'start_time' => 'Hora de Inicio',
            'end_time' => 'Hora de Fin',
            'canceled_at' => 'Fecha de Cancelación',
            'canceled_reason' => 'Motivo de Cancelación',
            'issued_at' => 'Fecha de Emisión',
        ];

        $valueTranslations = [
            'issued' => 'Pendiente/Emitida',
            'paid' => 'Pagada',
            'canceled' => 'Anulada',
            'draft' => 'Borrador',
            'reserved' => 'Reservada',
            'confirmed' => 'Confirmada',
            'in_service' => 'En Atención',
            'done' => 'Atendida',
            'no_show' => 'No Asistió',
            'non-attendance' => 'No Asistió',
            '0' => 'No',
            '1' => 'Sí',
        ];

        foreach ($changes as $field => $change) {
            // Skip redundant fields if action already implies them
            if ($this->auditable_type === 'App\\Models\\Invoice' && isset($changes['status'])) {
                if ($changes['status']['new'] === 'paid' && in_array($field, ['status', 'paid_at'])) continue;
                if ($changes['status']['new'] === 'canceled' && in_array($field, ['status'])) continue;
            }
            if ($this->auditable_type === 'App\\Models\\Appointment' && isset($changes['status'])) {
                if (in_array($changes['status']['new'], ['canceled', 'confirmed', 'in_service', 'done', 'no_show']) && in_array($field, ['status', 'is_active', 'canceled_at'])) continue;
            }

            $fieldName = $fieldTranslations[$field] ?? ucfirst(str_replace('_', ' ', $field));
            
            $oldVal = is_bool($change['old']) ? ($change['old'] ? 'Sí' : 'No') : $change['old'];
            $newVal = is_bool($change['new']) ? ($change['new'] ? 'Sí' : 'No') : $change['new'];

            $oldVal = $valueTranslations[$oldVal] ?? $oldVal ?? '(vacío)';
            $newVal = $valueTranslations[$newVal] ?? $newVal ?? '(vacío)';

            $formatted[] = [
                'field' => $fieldName,
                'old' => $oldVal,
                'new' => $newVal,
            ];
        }

        return $formatted;
    }
}
