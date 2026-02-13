<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Add `use Auditable;` to any model to automatically log
 * created, updated and deleted events.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        // After creating
        static::created(function ($model) {
            self::logAudit($model, 'created', [], $model->getAttributes());
        });

        // After updating
        static::updated(function ($model) {
            $dirty = $model->getDirty();
            if (empty($dirty)) return;

            $original = collect($model->getOriginal())
                ->only(array_keys($dirty))
                ->toArray();

            self::logAudit($model, 'updated', $original, $dirty);
        });

        // After deleting
        static::deleted(function ($model) {
            self::logAudit($model, 'deleted', $model->getAttributes(), []);
        });
    }

    /**
     * Get a human-readable label for audit logs.
     * Override in each model for better labels.
     */
    public function getAuditLabel(): string
    {
        // Try common name fields
        if (isset($this->first_name, $this->last_name)) {
            return trim("{$this->first_name} {$this->last_name}");
        }
        if (isset($this->name)) {
            return $this->name;
        }
        if (isset($this->title)) {
            return $this->title;
        }
        return "#{$this->id}";
    }

    /**
     * Fields to exclude from audit (passwords, tokens, etc.)
     * Override in model to customize.
     */
    public function getAuditExclude(): array
    {
        return ['password', 'remember_token', 'updated_at', 'created_at'];
    }

    private static function logAudit($model, string $action, array $old, array $new): void
    {
        try {
            $exclude = $model->getAuditExclude();

            $old = collect($old)->except($exclude)->toArray();
            $new = collect($new)->except($exclude)->toArray();

            AuditLog::create([
                'user_id'         => Auth::id(),
                'action'          => $action,
                'auditable_type'  => get_class($model),
                'auditable_id'    => $model->getKey(),
                'auditable_label' => $model->getAuditLabel(),
                'old_values'      => !empty($old) ? $old : null,
                'new_values'      => !empty($new) ? $new : null,
                'ip_address'      => Request::ip(),
            ]);
        } catch (\Throwable $e) {
            // Never break the app because of audit logging
            \Log::warning("[Audit] Error logging: " . $e->getMessage());
        }
    }
}
