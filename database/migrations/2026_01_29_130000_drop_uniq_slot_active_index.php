<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            // Strictly DROP the index. Do not recreate it.
            // The logic for overlap is handled in AppointmentController (Software Layer).
            // This prevents the 'Duplicate entry' error on cancellations.
             \Illuminate\Support\Facades\DB::statement("DROP INDEX uniq_slot_active ON appointments");
        } catch (\Exception $e) {
            // Ignore if it doesn't exist
             \Illuminate\Support\Facades\Log::info("Index uniq_slot_active not found or already dropped.");
        }
    }

    public function down(): void
    {
        try {
             \Illuminate\Support\Facades\DB::statement("DROP INDEX uniq_slot_active ON appointments");
             // Restore old buggy index for true rollback fidelity if required, 
             // but arguably we shouldn't restore a bug. 
             // Let's just restore the basic one if they rollback.
             \Illuminate\Support\Facades\DB::statement("CREATE UNIQUE INDEX uniq_slot_active ON appointments (dentist_id, date, start_time, is_active)");
        } catch (\Exception $e) {}
    }
};
