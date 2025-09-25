<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $t) {
            $t->foreignId('chair_id')->nullable()->after('dentist_id')
              ->constrained('chairs')->nullOnDelete();
            $t->index(['chair_id','day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $t) {
            $t->dropConstrainedForeignId('chair_id');
            $t->dropIndex(['chair_id','day_of_week']);
        });
    }
};
