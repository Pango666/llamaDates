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
            if (!Schema::hasColumn('consents','template_id')) {
                $t->unsignedBigInteger('template_id')->nullable()->after('patient_id');
                $t->foreign('template_id')->references('id')->on('consent_templates')->nullOnDelete();
            }
            if (!Schema::hasColumn('consents','file_path')) {
                $t->string('file_path')->nullable()->after('signature_path'); // PDF generado
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consents', function (Blueprint $t) {
            if (Schema::hasColumn('consents','template_id')) {
                try { $t->dropForeign(['template_id']); } catch (\Throwable $e) {}
                $t->dropColumn('template_id');
            }
            if (Schema::hasColumn('consents','file_path')) {
                $t->dropColumn('file_path');
            }
        });
    }
};
