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
        Schema::create('process_checkpoints_old', function (Blueprint $table) {
            $table->id();
            $table->string('company_code');
            $table->foreign('company_code')->references('company_code')->on('ms_company');
            $table->foreignId('periode_id')->constrained('ms_periode');
            $table->string('step');
            $table->string('status')->default('completed');
            $table->json('payload')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['company_code', 'periode_id', 'step']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_checkpoints_old');
    }
};
