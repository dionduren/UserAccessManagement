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
            $table->string('activity_type'); // e.g., 'create', 'update', 'delete', 'login', etc.
            $table->string('model_type')->nullable(); // Class name of the model being logged
            $table->unsignedBigInteger('model_id')->nullable(); // ID of the model being logged
            $table->json('before_data')->nullable(); // Data before the change (for update/delete)
            $table->json('after_data')->nullable(); // Data after the change (for create/update)
            $table->unsignedBigInteger('user_id')->nullable(); // ID of the user who performed the activity
            $table->string('ip_address')->nullable(); // IP address of the user
            $table->string('user_agent')->nullable(); // User agent of the user
            $table->timestamp('logged_at')->default(DB::raw('CURRENT_TIMESTAMP')); // Time of activity
            $table->timestamps();
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
