<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobRolesTable extends Migration
{
    public function up()
    {
        Schema::create('tr_job_roles', function (Blueprint $table) {
            $table->id();
            $table->string('company_id')->nullable();
            $table->string('kompartemen_id')->nullable();
            $table->string('departemen_id')->nullable();
            $table->string('job_role_id')->nullable();
            $table->string('nama'); // Job Role Name
            $table->string('status')->default('Active')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('error_kompartemen_id')->nullable();
            $table->string('error_kompartemen_name')->nullable();
            $table->string('error_departemen_id')->nullable();
            $table->string('error_departemen_name')->nullable();
            $table->boolean('flagged')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->text('deleted_by')->nullable();

            $table->index('company_id');
            $table->index('kompartemen_id');
            $table->index('departemen_id');
            $table->index('nama');
            $table->index('status');
            $table->index('deleted_at');

            // $table->foreign('company_id')->references('company_code')->on('ms_company')->onDelete('set null');
            // $table->foreign('kompartemen_id')->references('kompartemen_id')->on('ms_kompartemen')->onDelete('set null');
            // $table->foreign('departemen_id')->references('departemen_id')->on('ms_departemen')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tr_job_roles');
    }
}
