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
        Schema::create('ms_penomoran_uam', function (Blueprint $table) {
            $table->id();
            $table->string('company_id')->nullable();
            $table->string('kompartemen_id')->nullable();
            $table->string('departemen_id')->nullable();
            $table->string('unit_kerja_id');
            $table->integer('number')->unique();
            $table->softDeletes();
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
        Schema::dropIfExists('ms_penomoran_uam');
    }
};
