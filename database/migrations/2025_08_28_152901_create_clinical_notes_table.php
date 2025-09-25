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
        Schema::create('clinical_notes', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('patient_id');
            $t->unsignedBigInteger('appointment_id')->nullable();
            $t->string('type')->default('SOAP');
            $t->text('subjective')->nullable();
            $t->text('objective')->nullable();
            $t->text('assessment')->nullable();
            $t->text('plan')->nullable();
            $t->json('vitals')->nullable();
            $t->unsignedBigInteger('author_id')->nullable();
            $t->timestamps();

            $t->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
            $t->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();
            $t->foreign('author_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_notes');
    }
};
