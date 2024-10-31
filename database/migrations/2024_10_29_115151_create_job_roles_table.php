<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobRolesTable extends Migration
{
    public function up()
    {
        Schema::create('job_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->string('nama_jabatan'); // Job Role Name
            $table->text('deskripsi')->nullable();
            $table->foreignId('kompartemen_id')->nullable()->constrained('kompartemen')->onDelete('set null');
            $table->foreignId('departemen_id')->nullable()->constrained('departemen')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_roles');
    }
}
