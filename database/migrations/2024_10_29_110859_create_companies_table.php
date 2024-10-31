<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('company_code')->unique(); // Company code, unique (e.g., A000, B000)
            $table->string('name')->unique(); // Company name, unique
            $table->text('description')->nullable(); // Company description
            $table->timestamps(); // Created at and updated at timestamps
            $table->softDeletes(); // Soft delete for safe record deletion
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // User who created
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); // User who updated
        });
    }

    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
