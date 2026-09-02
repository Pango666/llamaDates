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
        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable()->after('patient_id');
            $table->unsignedBigInteger('dentist_id')->nullable()->after('service_id');
            $table->string('tooth_code', 3)->nullable()->after('dentist_id');
            $table->enum('surface', ['O', 'M', 'D', 'B', 'L', 'I'])->nullable()->after('tooth_code');

            $table->foreign('service_id')->references('id')->on('services')->restrictOnDelete();
            $table->foreign('dentist_id')->references('id')->on('dentists')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropForeign(['dentist_id']);
            $table->dropColumn(['service_id', 'dentist_id', 'tooth_code', 'surface']);
        });
    }
};
