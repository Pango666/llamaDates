<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','asistente','odontologo','paciente','cajero','almacen','enfermera') NOT NULL DEFAULT 'asistente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','asistente','odontologo','paciente') NOT NULL DEFAULT 'asistente'");
    }
};
