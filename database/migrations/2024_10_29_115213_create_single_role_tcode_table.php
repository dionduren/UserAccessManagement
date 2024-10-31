<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSingleRoleTcodeTable extends Migration
{
    public function up()
    {
        Schema::create('single_role_tcode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('single_role_id')->nullable()->constrained('single_roles')->onDelete('set null');
            $table->foreignId('tcode_id')->nullable()->constrained('tcodes')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('single_role_tcode');
    }
}
