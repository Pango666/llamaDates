<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Service extends Model
{
    use Auditable;
    protected $fillable = ['name', 'duration_min', 'price', 'active', 'specialty_id', 'discount_active', 'discount_type', 'discount_amount', 'discount_duration', 'created_at', 'updated_at', 'discount_start_at', 'discount_end_at'];

    protected $casts = [
        'breaks' => 'array',
    ];

    public function getDurationMinAttribute($value)
    {
        if (is_numeric($value) && (int)$value > 0) return (int)$value;

        // compatibilidad con duration_minutes
        $alt = $this->attributes['duration_min'] ?? null;
        if (is_numeric($alt) && (int)$alt > 0) return (int)$alt;

        return 30; // fallback seguro
    }

    public function discountIsActiveNow(?Carbon $at = null): bool
    {
        $at = $at ?: now();

        if (!(bool)($this->discount_active ?? false)) return false;

        $start = $this->discount_start_at;
        $end   = $this->discount_end_at;

        if ($start && $at->lt($start)) return false;
        if ($end && $at->gt($end)) return false;

        $amount = (float)($this->discount_amount ?? 0);
        if ($amount <= 0) return false;

        $type = strtolower((string)($this->discount_type ?? 'percent'));
        return in_array($type, ['percent', 'fixed'], true);
    }

    public function discountIsValidNow(): bool
    {
        if (!$this->discount_active) return false;

        $type = strtolower((string)($this->discount_type ?? ''));
        if (!in_array($type, ['fixed', 'percent'], true)) return false;

        if (!is_numeric($this->discount_amount)) return false;
        if ((float)$this->discount_amount <= 0) return false;

        $days = $this->discount_duration;
        if ($days === null) return true; // sin caducidad
        if (!is_numeric($days) || (int)$days <= 0) return true; // si vino mal, no bloquees

        $start = $this->updated_at ?? $this->created_at ?? now();
        $expiresAt = Carbon::parse($start)->addDays((int)$days)->endOfDay();

        return now()->lte($expiresAt);
    }

    public function discountIsActive(?Carbon $at = null): bool
    {
        $at = $at ?: now();

        if (!((bool)($this->discount_active ?? false))) {
            return false;
        }

        $start = $this->discount_start_at ? Carbon::parse($this->discount_start_at) : null;
        $end   = $this->discount_end_at   ? Carbon::parse($this->discount_end_at)   : null;

        if ($start && $at->lt($start)) return false;
        if ($end   && $at->gt($end))   return false;

        // seguridad: si hay tipo/monto inválidos, consideramos que no aplica
        $amt  = (float)($this->discount_amount ?? 0);
        if ($amt <= 0) return false;

        $type = strtolower((string)($this->discount_type ?? 'fixed'));
        if (!in_array($type, ['fixed', 'percent'], true)) return false;

        return true;
    }

    public function discountLabel(): ?string
    {
        if (!$this->discountIsActive()) return null;

        $type = strtolower((string)($this->discount_type ?? 'fixed'));
        $amt  = (float)($this->discount_amount ?? 0);

        if ($type === 'percent') return rtrim(rtrim(number_format($amt, 2, '.', ''), '0'), '.') . '%';
        return 'Bs ' . number_format($amt, 2);
    }

    public function discountValue(float $basePrice = null): float
    {
        $price = $basePrice ?? (float)($this->price ?? 0);

        if (!$this->discountIsValidNow()) return 0.0;

        $type = strtolower((string)$this->discount_type);
        $amount = (float)$this->discount_amount;

        if ($type === 'percent') {
            $amount = max(0, min(100, $amount));
            return max(0, $price * ($amount / 100));
        }

        // fixed
        return max(0, min($price, $amount));
    }

    public function priceEffective(?Carbon $at = null): float
    {
        $base = (float)($this->price ?? 0);

        if (!$this->discountIsActive($at)) {
            return round(max(0, $base), 2);
        }

        $amt  = (float)($this->discount_amount ?? 0);
        $type = strtolower((string)($this->discount_type ?? 'fixed'));

        if ($type === 'percent') {
            $pct = max(0, min(100, $amt));
            $final = $base * (1 - ($pct / 100));
            return round(max(0, $final), 2);
        }

        // fixed
        return round(max(0, $base - $amt), 2);
    }

    public function priceFinal(): float
    {
        $price = (float)($this->price ?? 0);
        $final = $price - $this->discountValue($price);
        return max(0, $final);
    }
}
