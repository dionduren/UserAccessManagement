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
        Schema::create('tr_user_ussm_nik', function (Blueprint $table) {
            $table->id();
            $table->string('user_code');
            $table->string('user_type');
            $table->string('license_type');
            $table->string('valid_from')->nullable();
            $table->string('valid_to')->nullable();
            $table->string('group')->nullable(); // nempel ke shortname ms_company
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
        Schema::dropIfExists('tr_user_ussm_nik');
    }
};
