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
        if (Schema::hasTable('consents') && !Schema::hasColumn('consents','appointment_id')) {
            Schema::table('consents', function (Blueprint $t) {
                $t->unsignedBigInteger('appointment_id')->nullable()->after('patient_id');
                $t->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();
            });
        }
        if (Schema::hasTable('odontograms') && !Schema::hasColumn('odontograms','appointment_id')) {
            Schema::table('odontograms', function (Blueprint $t) {
                $t->unsignedBigInteger('appointment_id')->nullable()->after('patient_id');
                $t->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();
            });
        }
        // (Opcional si no lo tuvieras)
        if (Schema::hasTable('diagnoses') && !Schema::hasColumn('diagnoses','appointment_id')) {
            Schema::table('diagnoses', function (Blueprint $t) {
                $t->unsignedBigInteger('appointment_id')->nullable()->after('patient_id');
                $t->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();
            });
        }
        if (Schema::hasTable('attachments') && !Schema::hasColumn('attachments','appointment_id')) {
            Schema::table('attachments', function (Blueprint $t) {
                $t->unsignedBigInteger('appointment_id')->nullable()->after('patient_id');
                $t->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['consents','odontograms','diagnoses','attachments'] as $tbl) {
            if (Schema::hasTable($tbl) && Schema::hasColumn($tbl,'appointment_id')) {
                Schema::table($tbl, function (Blueprint $t) {
                    try { $t->dropForeign([$t->getTable().'_appointment_id_foreign']); } catch (\Throwable $e) {}
                    $t->dropColumn('appointment_id');
                });
            }
        }
    }
};
