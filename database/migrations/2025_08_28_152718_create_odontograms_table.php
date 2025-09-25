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
        Schema::create('odontograms', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('patient_id');
            $t->date('date');
            $t->text('notes')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->boolean('is_current')->default(true)->index(); // <— NUEVO
            $t->timestamps();

            $t->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
            $t->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $t->index(['patient_id','date']);                  // útil para historia/filtrado
        });

        Schema::create('odontogram_teeth', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('odontogram_id');
            $t->string('tooth_code', 3); // 11..48; 51..85
            $t->string('status')->nullable();                  // sano | ausente | null
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->foreign('odontogram_id')->references('id')->on('odontograms')->cascadeOnDelete();
            $t->unique(['odontogram_id', 'tooth_code']);
        });

        Schema::create('odontogram_surfaces', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('odontogram_tooth_id');
            $t->enum('surface', ['O', 'M', 'D', 'B', 'L', 'I']); // dejamos 'I' por compatibilidad
            $t->string('condition');                              // caries | obturado | sellado
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->foreign('odontogram_tooth_id')->references('id')->on('odontogram_teeth')->cascadeOnDelete();
            $t->unique(['odontogram_tooth_id', 'surface', 'condition'], 'uniq_surface_cond');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odontogram_surfaces');
        Schema::dropIfExists('odontogram_teeth');
        Schema::dropIfExists('odontograms');
    }
};
