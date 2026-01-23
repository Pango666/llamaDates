<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('specialties', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->timestamps();
        });

        Schema::create('dentist_specialty', function (Blueprint $t) {
            $t->id();
            $t->foreignId('dentist_id')->constrained('dentists')->cascadeOnDelete();
            $t->foreignId('specialty_id')->constrained('specialties')->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['dentist_id', 'specialty_id'], 'uniq_dentist_specialty');
        });

        // services: specialty_id opcional
        Schema::table('services', function (Blueprint $t) {
            // Solo agregar si la columna NO existe ya
            if (!Schema::hasColumn('services', 'specialty_id')) {
                $t->foreignId('specialty_id')->nullable()->after('active')->constrained('specialties')->nullOnDelete();
            } else {
                // Si ya existe, solo hacerla nullable y agregar foreign key
                $t->unsignedBigInteger('specialty_id')->nullable()->change();
                $t->foreign('specialty_id')->references('id')->on('specialties')->nullOnDelete();
            }
        });

    }

    public function down(): void
    {
        // volver a poner columna specialty (texto)
        Schema::table('dentists', function (Blueprint $t) {
            $t->string('specialty')->nullable();
        });
        Schema::table('services', function (Blueprint $t) {
            $t->dropConstrainedForeignId('specialty_id');
        });
        Schema::dropIfExists('dentist_specialty');
        Schema::dropIfExists('specialties');
    }
};
