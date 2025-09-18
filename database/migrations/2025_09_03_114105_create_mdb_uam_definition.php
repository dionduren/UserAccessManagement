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
        Schema::create('mdb_composite_role', function (Blueprint $table) {
            $table->id();
            $table->string('composite_role');
            $table->string('definisi')->nullable();
            $table->timestamps();
        });


        Schema::create('mdb_single_role', function (Blueprint $table) {
            $table->id();
            $table->string('single_role');
            $table->string('definisi')->nullable();
            $table->timestamps();
        });


        Schema::create('mdb_tcode', function (Blueprint $table) {
            $table->id();
            $table->string('tcode');
            $table->string('definisi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mdb_composite_role');
        Schema::dropIfExists('mdb_single_role');
        Schema::dropIfExists('mdb_tcode');
    }
};
