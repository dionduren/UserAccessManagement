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
        Schema::create('auth_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event'); // login, logout, failed
            $table->string('username')->nullable(); // for failed logins when no user_id
            $table->string('email')->nullable(); // for failed logins when no user_id
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('session_id', 100)->nullable();
            $table->uuid('request_id')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'event']);
            $table->index('occurred_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_logs');
    }
};
