<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartemenTable extends Migration
{
    public function up()
    {
        Schema::create('departemen', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('kompartemen_id')->nullable()->constrained('kompartemen')->onDelete('set null'); // Link to kompartemen
            $table->string('name'); // Name of the department
            $table->text('description')->nullable(); // Optional description
            $table->timestamps(); // Created at and updated at timestamps
            $table->softDeletes(); // Soft delete for safe record deletion
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // User who created
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); // User who updated
        });
    }

    public function down()
    {
        Schema::dropIfExists('departemen');
    }
}
