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
        Schema::create('ms_penomoran_uar', function (Blueprint $table) {
            $table->id();
            $table->string('company_id')->nullable();
            $table->string('kompartemen_id')->nullable();
            $table->string('departemen_id')->nullable();
            $table->string('unit_kerja_id');
            $table->string('number');
            $table->softDeletes();
            $table->timestamps();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();

            $table->unique(['company_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_penomoran_uar');
    }
};
