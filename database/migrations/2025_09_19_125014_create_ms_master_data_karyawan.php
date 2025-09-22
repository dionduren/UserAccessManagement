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
        Schema::create('ms_master_data_karyawan', function (Blueprint $table) {
            $table->id();
            $table->string('nik');
            $table->string('nama');
            $table->string('company')->nullable();
            $table->string('direktorat_id')->nullable();
            $table->string('direktorat')->nullable();
            $table->string('kompartemen_id')->nullable();
            $table->string('kompartemen')->nullable();
            $table->string('departemen_id')->nullable();
            $table->string('departemen')->nullable();
            $table->string('atasan')->nullable();
            $table->string('cost_center')->nullable();
            $table->timestamps();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_master_data_karyawan');
    }
};
