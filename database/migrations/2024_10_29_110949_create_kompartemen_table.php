<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKompartemenTable extends Migration
{
    public function up()
    {
        Schema::create('ms_kompartemen', function (Blueprint $table) {
            $table->string('kompartemen_id')->primary(); // Primary key
            $table->string('company_id'); // foreign key
            $table->string('nama'); // Name of the compartment
            $table->text('deskripsi')->nullable(); // Optional description
            $table->string('cost_center')->nullable(); // Cost center associated with the compartment
            $table->timestamps(); // Created at and updated at timestamps
            $table->softDeletes(); // Soft delete for safe record deletion
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->text('deleted_by')->nullable();

            $table->index('kompartemen_id');
            $table->index('company_id');
            $table->index('deleted_at');


            // $table->foreign('company_id')->references('company_code')->on('ms_company')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ms_kompartemen');
    }
}
