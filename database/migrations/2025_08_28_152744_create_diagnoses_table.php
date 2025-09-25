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
        Schema::create('diagnoses', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('patient_id');
            $t->string('code')->nullable();
            $t->string('label');
            $t->string('tooth_code', 3)->nullable();
            $t->enum('surface', ['O', 'M', 'D', 'B', 'L', 'I'])->nullable();
            $t->enum('status', ['active', 'resolved'])->default('active');
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnoses');
    }
};
