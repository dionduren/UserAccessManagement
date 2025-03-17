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
            $table->foreignId('company_id')->nullable()->constrained('ms_company')->onDelete('set null');
            $table->foreignId('kompartemen_id')->nullable()->constrained('ms_kompartemen')->onDelete('set null');
            $table->foreignId('departemen_id')->nullable()->constrained('ms_departemen')->onDelete('set null');
            $table->string('nama_jabatan'); // Job Role Name
            $table->string('status');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->text('deleted_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tr_job_roles');
    }
}
