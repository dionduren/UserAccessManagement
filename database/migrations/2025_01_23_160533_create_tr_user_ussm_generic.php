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
        Schema::create('tr_user_ussm_generic', function (Blueprint $table) {
            $table->id();
            $table->string('user_code');
            $table->string('user_type');
            $table->string('cost_code');
            $table->string('license_type');
            $table->string('group')->nullable(); // nempel ke shortname ms_company
            $table->string('valid_from')->nullable();
            $table->string('valid_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->foreignId('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tr_user_ussm_generic');
    }
};
