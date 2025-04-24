<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompositeRolesTable extends Migration
{
    public function up()
    {
        Schema::create('tr_composite_roles', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('kompartemen_id')->nullable();
            $table->string('departemen_id')->nullable();
            $table->unsignedBigInteger('jabatan_id')->nullable();
            $table->string('nama'); // Composite Role Name
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->text('deleted_by')->nullable();

            $table->index('company_id');
            $table->index('jabatan_id');
            $table->index('nama');
            $table->index('deleted_at');

            $table->foreign('company_id')->references('company_code')->on('ms_company')->onDelete('set null');
            $table->foreign('jabatan_id')->references('id')->on('tr_job_roles')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tr_composite_roles');
    }
}
