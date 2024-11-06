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
        Schema::table('pt_single_role_tcode', function (Blueprint $table) {
            $table->foreign('single_role_id')->references('id')->on('tr_single_roles')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('tcode_id')->references('id')->on('tr_tcodes')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pt_single_role_tcode', function (Blueprint $table) {
            // Drop the foreign key constraints if they exist
            $table->dropForeign('fk_single_role_id');
            $table->dropForeign('fk_tcode_id');
        });
    }
};
