<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    public function up()
    {
        Schema::create('ms_company', function (Blueprint $table) {
            $table->string('company_code')->primary(); // Company code, unique (e.g., A000, B000)
            $table->string('nama')->unique(); // Company name, unique
            $table->string('shortname')->nullable(); // Company name, unique
            $table->text('deskripsi')->nullable(); // Company description
            $table->timestamps(); // Created at and updated at timestamps
            $table->softDeletes(); // Soft delete for safe record deletion
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->text('deleted_by')->nullable();

            $table->index('company_code');
            $table->index('nama');
            $table->index('shortname');
            $table->index('deleted_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ms_company');
    }
}
