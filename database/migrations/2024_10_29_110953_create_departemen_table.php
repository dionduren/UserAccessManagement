<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartemenTable extends Migration
{
    public function up()
    {
        Schema::create('ms_departemen', function (Blueprint $table) {
            $table->string('departemen_id')->primary(); // Primary key
            $table->string('company_id');
            $table->string('kompartemen_id')->nullable(); // Link to kompartemen
            $table->string('nama'); // Name of the department
            $table->text('deskripsi')->nullable(); // Optional description
            $table->timestamps(); // Created at and updated at timestamps
            $table->softDeletes(); // Soft delete for safe record deletion
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->text('deleted_by')->nullable();

            $table->index('departemen_id');
            $table->index('company_id');
            $table->index('kompartemen_id');
            $table->index('deleted_at');

            $table->foreign('company_id')->references('company_code')->on('ms_company')->onDelete('set null');
            $table->foreign('kompartemen_id')->references('kompartemen_id')->on('ms_kompartemen')->onDelete('set null'); // Link to kompartemen
        });
    }

    public function down()
    {
        Schema::dropIfExists('ms_departemen');
    }
}
