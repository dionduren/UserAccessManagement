<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartemenTable extends Migration
{
    public function up()
    {
        Schema::create('ms_departemen', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('company_id')->nullable()->constrained('ms_company')->onDelete('set null');
            $table->foreignId('kompartemen_id')->nullable()->constrained('ms_kompartemen')->onDelete('set null'); // Link to kompartemen
            $table->string('name'); // Name of the department
            $table->text('description')->nullable(); // Optional description
            $table->timestamps(); // Created at and updated at timestamps
            $table->softDeletes(); // Soft delete for safe record deletion
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->text('deleted_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ms_departemen');
    }
}
