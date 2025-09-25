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
        Schema::table('consents', function (Blueprint $t) {
            if (!Schema::hasColumn('consents','appointment_id')) {
                $t->unsignedBigInteger('appointment_id')->nullable()->after('patient_id');
                $t->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();
            }
            if (!Schema::hasColumn('consents','template_id')) {
                $t->unsignedBigInteger('template_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('consents','file_path')) {
                $t->string('file_path')->nullable()->after('signature_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consents', function (Blueprint $t) {
            if (Schema::hasColumn('consents','appointment_id')) {
                $t->dropForeign(['appointment_id']);
                $t->dropColumn('appointment_id');
            }
            if (Schema::hasColumn('consents','template_id')) {
                $t->dropColumn('template_id');
            }
            if (Schema::hasColumn('consents','file_path')) {
                $t->dropColumn('file_path');
            }
        });
    }
};
