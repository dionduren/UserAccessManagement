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
        Schema::create('ms_generic_unit_kerja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id');
            $table->string('user_cc');
            $table->string('kompartemen_id')->nullable();
            $table->string('departemen_id')->nullable();
            $table->string('error_kompartemen_id')->nullable();
            $table->string('error_departemen_id')->nullable();
            $table->boolean('flagged')->default(false)->nullable();
            $table->text('keterangan_flagged')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_generic_unit_kerja');
    }
};
