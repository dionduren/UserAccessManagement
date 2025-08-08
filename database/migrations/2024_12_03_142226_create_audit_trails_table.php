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
        Schema::create('log_audit_trails', function (Blueprint $table) {
            $table->id();
            // $table->string('activity_type'); // e.g., 'create', 'update', 'delete', 'login', etc.
            // $table->string('model_type')->nullable(); // Class name of the model being logged
            // $table->unsignedBigInteger('model_id')->nullable(); // ID of the model being logged
            // $table->json('before_data')->nullable(); // Data before the change (for update/delete)
            // $table->json('after_data')->nullable(); // Data after the change (for create/update)
            // $table->string('username')->nullable(); // ID of the user who performed the activity
            // $table->string('ip_address')->nullable(); // IP address of the user
            // $table->string('user_agent')->nullable(); // User agent of the user
            // $table->timestamp('logged_at')->default(DB::raw('CURRENT_TIMESTAMP')); // Time of activity
            // $table->timestamps();

            // Who
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('username')->nullable(); // optional cache of the username at the time

            // What
            $table->string('activity_type');              // create, update, delete, login, logout, etc.
            $table->string('model_type')->nullable();     // \App\Models\Something
            $table->unsignedBigInteger('model_id')->nullable();

            // Request context
            $table->string('route')->nullable();
            $table->string('method', 10)->nullable();
            $table->integer('status_code')->nullable();
            $table->string('session_id', 100)->nullable();
            $table->uuid('request_id')->nullable();

            // Where
            $table->string('ip_address', 45)->nullable(); // IPv4/IPv6
            $table->string('user_agent')->nullable();

            // Change sets
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();

            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();

            // Indexes
            $table->index(['model_type', 'model_id']);
            $table->index(['user_id', 'activity_type']);
            $table->index('logged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
