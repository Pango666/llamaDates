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
        Schema::create('suppliers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('contact')->nullable();
            $t->string('phone')->nullable();
            $t->string('tax_id')->nullable(); // NIT / RFC / RUC
            $t->timestamps();
        });

        Schema::create('locations', function (Blueprint $t) {
            $t->id();
            $t->string('name');              // Ej: "Depósito", "Box 1"
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('sku')->nullable()->unique(); // permite múltiples NULL en MySQL
            $t->string('name');
            $t->string('presentation')->nullable();  // "Caja x30", "Ampolla 2ml"
            $t->string('unit')->default('unidad');   // unidad de medida
            $t->string('brand')->nullable();         // laboratorio/marca
            $t->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $t->decimal('min_stock', 12, 3)->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

         Schema::create('inventory_movements', function (Blueprint $t) {
            $t->id();

            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->foreignId('location_id')->constrained('locations')->cascadeOnDelete();

            // in=ingreso, out=salida, adjust=ajuste, transfer=traslado (se registran 2 movimientos: - en origen, + en destino)
            $t->enum('type', ['in','out','adjust','transfer']);

            // Cantidad movida (signo NO se usa; el signo lo determina "type")
            $t->decimal('qty', 12, 3);

            // Costo unitario de la partida (sólo aplica a IN y ajustes de alta); nullable en OUT
            $t->decimal('unit_cost', 12, 4)->nullable();

            // Trazabilidad
            $t->string('lot')->nullable();
            $t->date('expires_at')->nullable();

            // Contexto/autoría
            $t->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $t->text('note')->nullable();
            $t->timestamps();

            // Índices útiles
            $t->index(['product_id','location_id']);
            $t->index(['product_id','lot']);
            $t->index(['expires_at']);
        });

        Schema::create('appointment_supplies', function (Blueprint $t) {
            $t->id();

            $t->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->foreignId('location_id')->constrained('locations')->cascadeOnDelete();

            // Lote consumido (si aplica). Si está vacío, se descuenta por FIFO de la ubicación.
            $t->string('lot')->nullable();

            // Cantidad consumida
            $t->decimal('qty', 12, 3);

            // Costo unitario referencial del momento de la salida (para auditoría/kardex valuado)
            $t->decimal('unit_cost_at_issue', 12, 4)->nullable();

            $t->timestamps();

            $t->index(['appointment_id','product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('appointment_supplies');
    }
};
