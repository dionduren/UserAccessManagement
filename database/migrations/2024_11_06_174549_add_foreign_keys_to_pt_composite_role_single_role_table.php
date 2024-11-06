<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pt_composite_role_single_role', function (Blueprint $table) {
            $table->foreign('composite_role_id')
                ->references('id')->on('tr_composite_roles')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->foreign('single_role_id')
                ->references('id')->on('tr_single_roles')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('pt_composite_role_single_role', function (Blueprint $table) {
            // Drop the foreign key constraints if they exist
            $table->dropForeign(['composite_role_id']);
            $table->dropForeign(['single_role_id']);
        });
    }
};
