<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTcodesTable extends Migration
{
    public function up()
    {
        Schema::create('tr_tcodes', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('company_id')->nullable()->constrained('ms_company')->onDelete('set null');
            $table->string('code'); // Tcode Identifier
            $table->string('sap_module')->nullable(); // Tcode Identifier
            $table->text('deskripsi')->nullable(); // Description
            $table->timestamps();
            $table->softDeletes();
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->text('deleted_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tr_tcodes');
    }
}
