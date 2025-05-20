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
        Schema::create('ms_terminated_employee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->nullable();
            $table->string('nik')->index();
            $table->string('nama');
            $table->date('tanggal_resign')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();
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
        Schema::dropIfExists('ms_terminated_employee');
    }
};
