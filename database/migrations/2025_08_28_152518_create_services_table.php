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
        Schema::create('services', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('name');
            $t->unsignedSmallInteger('duration_min'); // 15, 30, 45, 60...
            $t->decimal('price', 12, 2)->default(0);
            $t->boolean('active')->default(true);
            $t->unsignedBigInteger('specialty_id');
            $t->boolean('discount_active')->default(false);
            $t->enum('discount_type',['fixed','percent'])->nullable();
            $t->decimal('discount_amount')->nullable();
            $t->integer('discount_duration')->nullable();
            $t->timestamp('discount_start_at')->nullable();
            $t->timestamp('discount_end_at')->nullable();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
