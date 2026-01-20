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
        Schema::create('treatment_plans', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('patient_id');
            $t->string('title')->default('Plan de tratamiento');
            $t->decimal('estimate_total', 12, 2)->default(0);
            $t->enum('status', ['draft', 'approved', 'in_progress', 'completed', 'canceled'])->default('draft');
            $t->timestamp('approved_at')->nullable();
            $t->unsignedBigInteger('approved_by')->nullable();
            $t->timestamps();

            $t->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->cascadeOnDelete();

            $t->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('treatments', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('treatment_plan_id');
            $t->unsignedBigInteger('service_id');

            // 🔹 NUEVO: odontólogo asignado al tratamiento (planificado)
            $t->unsignedBigInteger('dentist_id')->nullable();

            // 🔹 NUEVO: planificación de cita (no es la cita real aún)
            $t->date('planned_date')->nullable();
            $t->time('planned_start_time')->nullable();
            $t->time('planned_end_time')->nullable();

            $t->string('tooth_code', 3)->nullable();
            $t->enum('surface', ['O', 'M', 'D', 'B', 'L', 'I'])->nullable();
            $t->decimal('price', 12, 2)->default(0);
            $t->enum('status', ['planned', 'in_progress', 'done', 'canceled'])->default('planned');
            $t->unsignedBigInteger('appointment_id')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->foreign('treatment_plan_id')
                ->references('id')
                ->on('treatment_plans')
                ->cascadeOnDelete();

            $t->foreign('service_id')
                ->references('id')
                ->on('services')
                ->restrictOnDelete();

            // 🔹 FK al odontólogo (nullable)
            $t->foreign('dentist_id')
                ->references('id')
                ->on('dentists')
                ->nullOnDelete();

            $t->foreign('appointment_id')
                ->references('id')
                ->on('appointments')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatments');
        Schema::dropIfExists('treatment_plans');
    }
};
