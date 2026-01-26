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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('min_stock')->change();
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->integer('qty')->change();
        });

        Schema::table('appointment_supplies', function (Blueprint $table) {
            $table->integer('qty')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('min_stock', 12, 3)->change();
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->decimal('qty', 12, 3)->change();
        });

        Schema::table('appointment_supplies', function (Blueprint $table) {
            $table->decimal('qty', 12, 3)->change();
        });
    }
};
