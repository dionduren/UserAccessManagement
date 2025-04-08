<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pt_single_role_tcode', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('single_role_id')->nullable();
            $table->string('tcode_id')->nullable(); // Fix: must match tr_tcodes PK
            $table->timestamps();
            $table->softDeletes();
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->text('deleted_by')->nullable();

            // Indexes for FK speed
            $table->index('single_role_id');
            $table->index('tcode_id');
            $table->index('deleted_at');

            // Foreign keys
            $table->foreign('single_role_id')->references('id')->on('tr_single_roles')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('tcode_id')->references('code')->on('tr_tcodes')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pt_single_role_tcode');
    }
};
