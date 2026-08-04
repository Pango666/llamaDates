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
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','asistente','odontologo','paciente') NOT NULL DEFAULT 'asistente'");
        }

        DB::statement("UPDATE users SET role='asistente' WHERE role='recepcion'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE users SET role='recepcion' WHERE role='asistente'");

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','recepcion','odontologo','paciente') NOT NULL DEFAULT 'recepcion'");
        }
    }
};
