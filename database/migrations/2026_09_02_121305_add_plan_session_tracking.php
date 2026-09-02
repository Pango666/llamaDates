<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar tracking de sesiones al plan
        Schema::table('treatment_plans', function (Blueprint $t) {
            $t->unsignedInteger('total_sessions')->default(0)->after('estimate_total');
            $t->unsignedInteger('completed_sessions')->default(0)->after('total_sessions');
        });

        // Vincular citas directamente al plan y tratamiento
        Schema::table('appointments', function (Blueprint $t) {
            $t->unsignedBigInteger('treatment_plan_id')->nullable()->after('notes');
            $t->unsignedBigInteger('treatment_id')->nullable()->after('treatment_plan_id');

            $t->foreign('treatment_plan_id')
                ->references('id')
                ->on('treatment_plans')
                ->nullOnDelete();

            $t->foreign('treatment_id')
                ->references('id')
                ->on('treatments')
                ->nullOnDelete();
        });

        // Backfill: vincular citas existentes que ya están referenciadas desde treatments
        $treatments = \App\Models\Treatment::whereNotNull('appointment_id')->get();
        foreach ($treatments as $treatment) {
            \App\Models\Appointment::where('id', $treatment->appointment_id)
                ->update([
                    'treatment_plan_id' => $treatment->treatment_plan_id,
                    'treatment_id' => $treatment->id,
                ]);
        }

        // Backfill: total_sessions = count of treatments per plan
        $plans = \App\Models\TreatmentPlan::withCount('treatments')->get();
        foreach ($plans as $plan) {
            $completed = $plan->treatments()->where('status', 'done')->count();
            $plan->update([
                'total_sessions' => $plan->treatments_count,
                'completed_sessions' => $completed,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $t) {
            $t->dropForeign(['treatment_plan_id']);
            $t->dropForeign(['treatment_id']);
            $t->dropColumn(['treatment_plan_id', 'treatment_id']);
        });

        Schema::table('treatment_plans', function (Blueprint $t) {
            $t->dropColumn(['total_sessions', 'completed_sessions']);
        });
    }
};
