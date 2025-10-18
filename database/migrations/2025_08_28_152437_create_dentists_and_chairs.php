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
        Schema::create('chairs', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('name');
            $t->enum('shift', ['mañana', 'tarde', 'completo'])->default('completo');
            $t->timestamps();
        });

        Schema::create('dentists', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('name');
            $t->string('specialty')->nullable();
            $t->unsignedBigInteger('chair_id')->nullable();
            $t->boolean('status')->default(true);
            $t->timestamps();

            $t->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $t->foreign('chair_id')->references('id')->on('chairs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dentists');
        Schema::dropIfExists('chairs');
    }
};
