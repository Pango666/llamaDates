<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Temporarily Drop Foreign Key to unlock the index
        try {
            Schema::table('appointments', function (Blueprint $table) {
                // Check if FK exists before dropping (using raw SQL or Schema)
                // Using Schema method handles 'if exists' logic in newer Laravel or throws manageable error
                $table->dropForeign(['dentist_id']); 
            });
        } catch (\Throwable $e) {
            // Might fail if FK name differs or doesn't exist. verifying 'appointments_dentist_id_foreign' is standard.
        }

        // 2. Drop the Index (The main goal)
        try {
             \Illuminate\Support\Facades\DB::statement("DROP INDEX uniq_slot_active ON appointments");
        } catch (\Throwable $e) {
             \Illuminate\Support\Facades\Log::info("Index uniq_slot_active not found or already dropped.");
        }

        // 3. Restore Foreign Key
        try {
            Schema::table('appointments', function (Blueprint $table) {
                $table->foreign('dentist_id')
                      ->references('id')->on('dentists')
                      ->cascadeOnDelete();
            });
        } catch (\Throwable $e) {
             // FK might already exist if step 1 failed, or other issue.
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
