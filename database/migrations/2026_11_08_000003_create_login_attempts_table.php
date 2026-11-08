<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Estructura requerida para HU-05 (registro de intentos de inicio de sesión).
     */
    public function up(): void
    {
        if (!Schema::hasTable('login_attempts')) {
            Schema::create('login_attempts', function (Blueprint $table) {
                $table->id();
                $table->string('email');
                $table->string('ip_address', 45);
                $table->boolean('was_success')->default(false);
                $table->dateTime('attempted_at')->useCurrent();

                $table->index(['email', 'ip_address', 'attempted_at'], 'idx_login_attempts_lookup');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};

