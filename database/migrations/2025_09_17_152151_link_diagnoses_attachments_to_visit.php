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
        // Diagnósticos -> cita / nota
        Schema::table('diagnoses', function (Blueprint $t) {
            if (!Schema::hasColumn('diagnoses','appointment_id')) {
                $t->unsignedBigInteger('appointment_id')->nullable()->after('patient_id');
                $t->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();
            }
            if (!Schema::hasColumn('diagnoses','clinical_note_id')) {
                $t->unsignedBigInteger('clinical_note_id')->nullable()->after('appointment_id');
                $t->foreign('clinical_note_id')->references('id')->on('clinical_notes')->nullOnDelete();
            }
        });

        // Adjuntos -> nota (ya tienes appointment_id)
        Schema::table('attachments', function (Blueprint $t) {
            if (!Schema::hasColumn('attachments','clinical_note_id')) {
                $t->unsignedBigInteger('clinical_note_id')->nullable()->after('appointment_id');
                $t->foreign('clinical_note_id')->references('id')->on('clinical_notes')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diagnoses', function (Blueprint $t) {
            if (Schema::hasColumn('diagnoses','clinical_note_id')) {
                $t->dropForeign(['clinical_note_id']);
                $t->dropColumn('clinical_note_id');
            }
            if (Schema::hasColumn('diagnoses','appointment_id')) {
                $t->dropForeign(['appointment_id']);
                $t->dropColumn('appointment_id');
            }
        });

        Schema::table('attachments', function (Blueprint $t) {
            if (Schema::hasColumn('attachments','clinical_note_id')) {
                $t->dropForeign(['clinical_note_id']);
                $t->dropColumn('clinical_note_id');
            }
        });
    }
};
