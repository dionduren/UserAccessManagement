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
            $table->foreignId('periode_id')->nullable();
            $table->string('nama');
            $table->string('nik');
            $table->string('company_id')->nullable();
            $table->string('direktorat')->nullable();
            $table->string('kompartemen_id')->nullable();
            $table->string('departemen_id')->nullable();
            $table->string('atasan')->nullable();
            $table->string('cost_center')->nullable();
            $table->string('error_kompartemen_id')->nullable();
            $table->string('error_kompartemen_name')->nullable();
            $table->string('error_departemen_id')->nullable();
            $table->string('error_departemen_name')->nullable();
            $table->boolean('flagged')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();


            // $table->foreign('company_id')->references('company_code')->on('ms_company')->onDelete('set null');
            // $table->foreign('kompartemen_id')->references('kompartemen_id')->on('ms_kompartemen')->onDelete('set null');
            // $table->foreign('departemen_id')->references('departemen_id')->on('ms_departemen')->onDelete('set null');
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
