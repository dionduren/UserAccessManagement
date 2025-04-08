<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTcodesTable extends Migration
{
    public function up()
    {
        Schema::create('tr_tcodes', function (Blueprint $table) {
            $table->string('code')->primary(); // Tcode Identifier
            $table->string('sap_module')->nullable(); // SAP Module Name
            $table->text('deskripsi')->nullable(); // Description
            $table->timestamps();
            $table->softDeletes();
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->text('deleted_by')->nullable();

            $table->index('code');
            $table->index('sap_module');
            $table->index('deleted_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tr_tcodes');
    }
}
