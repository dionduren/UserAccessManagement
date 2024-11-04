<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    public function up()
    {
        Schema::create('ms_company', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('company_code')->unique(); // Company code, unique (e.g., A000, B000)
            $table->string('name')->unique(); // Company name, unique
            $table->text('description')->nullable(); // Company description
            $table->timestamps(); // Created at and updated at timestamps
            $table->softDeletes(); // Soft delete for safe record deletion
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->boolean('is_deleted')->default(0);
            $table->text('deleted_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ms_company');
    }
}
