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
            $table->unsignedBigInteger('tcode_id')->nullable(); // Changed to match tr_tcodes id column
            $table->string('source')->nullable(); // e.g., 'SAP', 'Manual'
            $table->timestamps();
            $table->softDeletes();
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->text('deleted_by')->nullable();

            // Indexes
            $table->index('single_role_id');
            $table->index('tcode_id');
            $table->index('deleted_at');

            // Unique Values
            $table->unique(['single_role_id', 'tcode_id'], 'unique_role_tcode');

            // Foreign keys
            $table->foreign('single_role_id')
                ->references('id')
                ->on('tr_single_roles')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('tcode_id')
                ->references('id')  // Now referencing the id column
                ->on('tr_tcodes')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pt_single_role_tcode');
    }
};
