<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Plantillas de consentimiento
        Schema::create('consent_templates', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('name');
            $t->longText('body');
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        // 2) Consentimientos (con mejoras)
        Schema::create('consents', function (Blueprint $t) {
            $t->bigIncrements('id');

            $t->unsignedBigInteger('patient_id');
            $t->unsignedBigInteger('template_id')->nullable();

            $t->string('title');
            $t->longText('body');

            $t->timestamp('signed_at')->nullable();
            $t->string('signed_by_name')->nullable();
            $t->string('signed_by_doc')->nullable();
            $t->string('signature_path')->nullable(); // (si luego firmas digital)
            $t->string('file_path')->nullable();      // escaneo firmado (PDF/JPG/PNG)

            $t->timestamps();

            $t->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
            $t->foreign('template_id')->references('id')->on('consent_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
        Schema::dropIfExists('consent_templates');
    }
};
