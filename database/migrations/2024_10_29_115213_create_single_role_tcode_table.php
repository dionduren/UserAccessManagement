<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSingleRoleTcodeTable extends Migration
{
    public function up()
    {
        Schema::create('pt_single_role_tcode', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('single_role_id')->nullable();
            $table->unsignedBigInteger('tcode_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->text('deleted_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pt_single_role_tcode');
    }
}
