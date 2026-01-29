<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE appointments DROP INDEX uniq_slot_active");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Could not drop index uniq_slot_active: " . $e->getMessage());
        }
    }

    public function down(): void
    {
    }
};
