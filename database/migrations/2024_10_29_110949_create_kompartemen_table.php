<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKompartemenTable extends Migration
{
    public function up()
    {
        Schema::create('kompartemen', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->string('name'); // Name of the compartment
            $table->text('description')->nullable(); // Optional description
            $table->timestamps(); // Created at and updated at timestamps
            $table->softDeletes(); // Soft delete for safe record deletion
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // User who created
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); // User who updated
        });
    }

    public function down()
    {
        Schema::dropIfExists('kompartemen');
    }
}
