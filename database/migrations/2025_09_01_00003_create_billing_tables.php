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
        Schema::create('payment_methods', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('invoices', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('number')->unique();                 // Ej: FAC-000001
            $t->unsignedBigInteger('patient_id');
            $t->unsignedBigInteger('appointment_id')->nullable();
            $t->unsignedBigInteger('treatment_plan_id')->nullable();

            $t->enum('status', ['draft', 'issued', 'paid', 'canceled'])->default('issued');
            $t->decimal('discount', 12, 2)->default(0);     // descuento absoluto
            $t->decimal('tax_percent', 5, 2)->default(0);   // % IVA/IT u otro

            $t->timestamp('issued_at')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->text('notes')->nullable();

            $t->unsignedBigInteger('created_by')->nullable();

            $t->timestamps();

            $t->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
            $t->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();
            $t->foreign('treatment_plan_id')->references('id')->on('treatment_plans')->nullOnDelete();
            $t->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });


        Schema::create('invoice_items', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('invoice_id');
            $t->unsignedBigInteger('service_id')->nullable();
            $t->unsignedBigInteger('treatment_id')->nullable();
            $t->string('description');             // texto libre (se muestra siempre)
            $t->unsignedInteger('quantity')->default(1);
            $t->decimal('unit_price', 12, 2);     // precio unitario al momento de facturar
            $t->decimal('total', 12, 2);          // quantity * unit_price

            $t->timestamps();

            $t->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            $t->foreign('service_id')->references('id')->on('services')->nullOnDelete();
            $t->foreign('treatment_id')->references('id')->on('treatments')->nullOnDelete();
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('invoice_id');
            $t->decimal('amount', 12, 2);
            $t->enum('method', ['cash', 'card', 'transfer', 'wallet'])->default('cash');
            $t->string('reference')->nullable();   // nro de voucher/tx
            $t->timestamp('paid_at')->nullable();
            $t->unsignedBigInteger('received_by')->nullable();

            $t->timestamps();

            $t->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            $t->foreign('received_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payment_methods');
    }
};
