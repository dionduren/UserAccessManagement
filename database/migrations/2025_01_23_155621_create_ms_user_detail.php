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
        Schema::create('ms_user_detail', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nik');
            $table->foreignId('company_id')->nullable()->constrained('ms_company')->onDelete('set null');
            $table->string('direktorat')->nullable();
            $table->foreignId('kompartemen_id')->nullable()->constrained('ms_kompartemen')->onDelete('set null');
            $table->foreignId('departemen_id')->nullable()->constrained('ms_departemen')->onDelete('set null');
            $table->string('email')->nullable();
            $table->string('grade')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('atasan')->nullable();
            $table->string('cost_center')->nullable();
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
        Schema::dropIfExists('ms_user_detail');
    }
};
