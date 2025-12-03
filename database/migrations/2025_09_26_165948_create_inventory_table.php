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
        // PROVEEDORES / LABORATORIOS
        Schema::create('suppliers', function (Blueprint $t) {
            $t->id();
            $t->string('name');                // Proveedor / laboratorio
            $t->string('contact')->nullable();
            $t->string('phone')->nullable();
            $t->string('tax_id')->nullable();  // NIT / RFC / RUC
            $t->timestamps();
        });

        // CATEGORÍAS DE PRODUCTOS (analgésico, antibiótico, etc.)
        Schema::create('product_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');              // Ej: "Analgésico", "Antibiótico"
            $t->string('code')->nullable();  // Opcional: "ANALG", "ATB"
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        // UNIDADES PARA PRESENTACIÓN (ampolla, tableta, jarabe…)
        Schema::create('product_presentation_units', function (Blueprint $t) {
            $t->id();
            $t->string('name');              // Ej: "Ampolla", "Tableta", "Jarabe", "Frasco"
            $t->string('short_name')->nullable(); // Ej: "amp", "tab"
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        // UNIDADES DE MEDIDA PARA CONCENTRACIÓN (mg, g, ml, %…)
        Schema::create('measurement_units', function (Blueprint $t) {
            $t->id();
            $t->string('name');              // Ej: "Miligramo", "Gramo", "Mililitro"
            $t->string('symbol');            // Ej: "mg", "g", "ml", "%"
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        // UBICACIONES (DEPÓSITO, BOX, ETC.)
        Schema::create('locations', function (Blueprint $t) {
            $t->id();
            $t->string('name');              // Ej: "Depósito", "Box 1"
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        // PRODUCTOS / MEDICAMENTOS
        Schema::create('products', function (Blueprint $t) {
            $t->id();

            // Identificación
            $t->string('sku')->nullable()->unique();       // Código interno opcional
            $t->string('barcode')->nullable()->unique();   // Código de barras

            $t->string('name');                            // Nombre del producto / medicamento

            // Categoría: analgésico, antibiótico, etc.
            $t->foreignId('product_category_id')
                ->nullable()
                ->constrained('product_categories')
                ->nullOnDelete();

            // Presentación normalizada (ampolla, tableta, jarabe…)
            $t->foreignId('presentation_unit_id')
                ->nullable()
                ->constrained('product_presentation_units')
                ->nullOnDelete();

            // Detalle de presentación: "Caja x30", "Frasco 120 ml", etc.
            $t->string('presentation_detail')->nullable();

            // Concentración normalizada: valor + unidad (mg, ml, etc.)
            $t->decimal('concentration_value', 12, 3)->nullable();  // Ej: 500.000
            $t->foreignId('concentration_unit_id')
                ->nullable()
                ->constrained('measurement_units')
                ->nullOnDelete();                                  // Ej: "mg"

            // Unidad base para stock (unidad, caja, frasco…)
            $t->string('unit')->default('unidad');

            // Laboratorio / marca
            $t->string('brand')->nullable();

            // Proveedor principal
            $t->foreignId('supplier_id')
                ->nullable()
                ->constrained('suppliers')
                ->nullOnDelete();

            // Stock mínimo y estado
            $t->integer('stock')->default(0);
            $t->decimal('min_stock', 12, 3)->default(0);
            $t->boolean('is_active')->default(true);

            $t->timestamps();
        });

        // MOVIMIENTOS DE INVENTARIO
        Schema::create('inventory_movements', function (Blueprint $t) {
            $t->id();

            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->foreignId('location_id')->constrained('locations')->cascadeOnDelete();

            // in=ingreso, out=salida, adjust=ajuste, transfer=traslado
            $t->enum('type', ['in','out','adjust','transfer']);

            // Cantidad movida (signo NO se usa; el signo lo determina "type")
            $t->decimal('qty', 12, 3);

            // Precio de compra unitario (para ingresos y ajustes de alta)
            $t->decimal('unit_cost', 12, 4)->nullable();

            // Factura / documento de compra
            $t->string('purchase_invoice_number')->nullable(); // Número de factura de compra

            // Trazabilidad por lote
            $t->string('lot')->nullable();
            $t->date('expires_at')->nullable();               // Fecha de caducidad

            // Contexto/autoría
            $t->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $t->text('note')->nullable();
            $t->timestamps();

            // Índices útiles
            $t->index(['product_id','location_id']);
            $t->index(['product_id','lot']);
            $t->index(['expires_at']);
            $t->index(['purchase_invoice_number']);
        });

        // INSUMOS ASOCIADOS A UNA CITA
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
        // Dropear primero las tablas que dependen de otras
        Schema::dropIfExists('appointment_supplies');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('products');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('measurement_units');
        Schema::dropIfExists('product_presentation_units');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('suppliers');
    }
};
