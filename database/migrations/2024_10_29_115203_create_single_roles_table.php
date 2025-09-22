<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSingleRolesTable extends Migration
{
    public function up()
    {
        Schema::create('tr_single_roles', function (Blueprint $table) {
            $table->id();
            // $table->string('company_id');
            $table->string('nama'); // Single Role Name
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->text('deleted_by')->nullable();

            // $table->index('company_id');
            $table->index('nama');
            $table->index('deleted_at');

            // $table->foreign('company_id')->references('company_code')->on('ms_company')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tr_single_roles');
    }
}
