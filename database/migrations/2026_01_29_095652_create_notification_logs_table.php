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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('appointment_id')->nullable()->index(); // Clave para evitar duplicados
            
            $table->string('channel')->index(); // email, push, whatsapp
            $table->string('type')->index();    // reminder, confirmed, etc.
            
            $table->string('recipient');        // email address, token, phone
            $table->string('status')->default('pending'); // sent, failed, pending
            
            $table->text('payload')->nullable();       // JSON con titulo/body
            $table->text('error_message')->nullable(); // Si falla
            
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
