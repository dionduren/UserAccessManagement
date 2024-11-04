<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSingleRoleTcodeTable extends Migration
{
    public function up()
    {
        Schema::create('vw_single_role_tcode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('single_role_id')->constrained('tr_single_roles')->onDelete('cascade');
            $table->foreignId('tcode_id')->constrained('tr_tcodes')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->boolean('is_deleted')->default(0);
            $table->text('deleted_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vw_single_role_tcode');
    }
}
