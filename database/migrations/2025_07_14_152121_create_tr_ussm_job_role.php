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
        Schema::create('tr_ussm_job_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->nullable();
            $table->string('nik')->nullable();
            $table->string('job_role_id')->nullable();
            $table->string('definisi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_update')->nullable();
            $table->boolean('flagged')->default(false)->nullable();
            $table->text('keterangan_flagged')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tr_ussm_job_role');
    }
};
