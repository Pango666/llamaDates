<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AppointmentsMarkNonAttendance extends Command
{
    protected $signature = 'appointments:mark-non-attendance {--chunk=500}';
    protected $description = 'Pone en non-attendance las citas reserved/confirmed que ya vencieron según end_time';

    public function handle(): int
    {
        $now   = Carbon::now();
        $chunk = max(1, (int) $this->option('chunk'));

        // Solo reserved/confirmed activas y que ya terminaron (date + end_time < now)
        $query = Appointment::query()
            ->whereIn('status', ['reserved', 'confirmed'])
            ->where('is_active', true)
            ->whereNull('canceled_at')
            ->whereRaw("TIMESTAMP(`date`, `end_time`) < ?", [$now->toDateTimeString()]);

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('No hay citas vencidas para marcar.');
            return self::SUCCESS;
        }

        $updated = 0;

        $query->orderBy('id')->chunkById($chunk, function ($rows) use (&$updated) {
            $ids = $rows->pluck('id')->all();

            $affected = Appointment::query()
                ->whereIn('id', $ids)
                ->whereIn('status', ['reserved', 'confirmed'])
                ->update([
                    'status'    => 'non-attendance',
                    'is_active' => false,
                ]);

            $updated += $affected;
        });

        $this->info("Encontradas: {$total} | Actualizadas: {$updated}");
        return self::SUCCESS;
    }
}
