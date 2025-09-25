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
        Schema::create('medical_histories', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('patient_id');
            $t->boolean('smoker')->default(false);
            $t->boolean('pregnant')->nullable();
            $t->text('allergies')->nullable();
            $t->text('medications')->nullable();
            $t->text('systemic_diseases')->nullable();
            $t->text('surgical_history')->nullable();
            $t->text('habits')->nullable();
            $t->json('extra')->nullable();
            $t->timestamps();

            $t->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
            $t->unique('patient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_histories');
    }
};
