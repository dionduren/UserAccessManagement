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
        Schema::create('ms_cost_center', function (Blueprint $table) {
            $table->id();
            $table->string('company_id')->nullable();
            $table->string('parent_id')->nullable();
            $table->string('level');
            $table->string('level_id');
            $table->string('level_name');
            $table->string('cost_center')->index();
            $table->string('cost_code')->index()->nullable();
            $table->text('deskripsi')->nullable();
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
        Schema::dropIfExists('ms_cost_center');
    }
};
