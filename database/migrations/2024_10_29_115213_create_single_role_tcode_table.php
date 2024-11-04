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
            $table->foreignId('single_role_id')->nullable()->constrained('tr_single_roles')->onDelete('set null');
            $table->foreignId('tcode_id')->nullable()->constrained('tr_tcodes')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vw_single_role_tcode');
    }
}
