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
        Schema::create('mdb_uam_relationship_raw', function (Blueprint $table) {
            $table->id();
            $table->string('sap_user');
            $table->string('composite_role');
            $table->string('composite_role_desc')->nullable();
            $table->string('single_role')->nullable();
            $table->string('single_role_desc')->nullable();
            $table->string('tcode')->nullable();
            $table->string('tcode_desc')->nullable();
            $table->timestamps();

            $table->index(['sap_user']);
            $table->index(['composite_role']);
            $table->index(['single_role']);
            $table->index(['tcode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mdb_uam_relationship_raw');
    }
};
