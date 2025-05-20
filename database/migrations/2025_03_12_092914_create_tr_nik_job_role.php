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
        Schema::create('tr_nik_job_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->nullable();
            $table->string('nik')->nullable();
            $table->string('job_role_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_update')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tr_nik_job_role');
    }
};
