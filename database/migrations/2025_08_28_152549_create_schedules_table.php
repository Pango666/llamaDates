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
        Schema::create('schedules', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('dentist_id');
            $t->unsignedTinyInteger('day_of_week'); // 0=Dom .. 6=Sáb
            $t->time('start_time');
            $t->time('end_time');
            $t->json('breaks')->nullable(); // [{start:"12:00", end:"12:30"}]
            $t->timestamps();

            $t->foreign('dentist_id')->references('id')->on('dentists')->cascadeOnDelete();
            $t->unique(['dentist_id', 'day_of_week', 'start_time', 'end_time'], 'uniq_sched');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
