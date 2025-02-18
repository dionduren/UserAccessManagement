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
        Schema::create('ms_sap_license_type', function (Blueprint $table) {
            $table->id();
            $table->string('license_type');
            $table->string('contract_license_type');
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tr_user_license_management');
        Schema::dropIfExists('ms_sap_license_type');
    }
};
