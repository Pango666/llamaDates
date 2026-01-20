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
        Schema::create('appointments', function (Blueprint $t) {
            $t->bigIncrements('id');

            $t->unsignedBigInteger('patient_id');
            $t->unsignedBigInteger('dentist_id');
            $t->unsignedBigInteger('service_id');
            $t->unsignedBigInteger('chair_id')->nullable();

            $t->date('date');
            $t->time('start_time');
            $t->time('end_time'); // calculada con duration_min del servicio

            $t->enum('status', ['reserved', 'confirmed', 'in_service', 'done', 'no_show', 'canceled', 'non-attendance'])
                ->default('reserved');

            $t->boolean('is_active')->default(true); // cuenta para disponibilidad
            $t->timestamp('canceled_at')->nullable();
            $t->foreignId('canceled_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('canceled_reason', 255)->nullable();

            $t->text('notes')->nullable();
            $t->timestamps();

            // FKs
            $t->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
            $t->foreign('dentist_id')->references('id')->on('dentists')->cascadeOnDelete();
            $t->foreign('service_id')->references('id')->on('services')->restrictOnDelete();
            $t->foreign('chair_id')->references('id')->on('chairs')->nullOnDelete();
        });

        // Índice único SOLO para activas: evita dobles reservas en el mismo slot
        Schema::table('appointments', function (Blueprint $t) {
            $t->unique(['dentist_id', 'date', 'start_time', 'is_active'], 'uniq_slot_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
