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
        Schema::create('ms_penomoran_job_role', function (Blueprint $table) {
            $table->id();
            $table->char('company_id', 4);
            $table->integer('last_number')->default(1);
            $table->timestamps();
            $table->unique('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_penomoran_job_role');
    }
};
