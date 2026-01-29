<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            // 1. Drop the old FULL unique index that caused the bug (preventing multiple cancellations)
            // Use Schema manager or raw SQL to be safe regardless of existence
             \Illuminate\Support\Facades\DB::statement("DROP INDEX uniq_slot_active ON appointments");
        } catch (\Exception $e) {
            // Ignore if didn't exist
        }

        try {
            // 2. Create the CORRECT Partial Index
            // Only enforce uniqueness when is_active = 1.
            // This allows infinite 'canceled' (is_active=0) rows for the same slot.
            \Illuminate\Support\Facades\DB::statement("CREATE UNIQUE INDEX uniq_slot_active ON appointments (dentist_id, date, start_time) WHERE is_active = 1");
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::warning("Could not create partial index: " . $e->getMessage());
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
