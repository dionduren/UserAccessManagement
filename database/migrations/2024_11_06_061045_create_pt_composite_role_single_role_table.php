<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pt_composite_role_single_role', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('single_role_id')->nullable();
            $table->unsignedBigInteger('composite_role_id')->nullable();
            $table->string('source')->nullable(); // e.g., 'SAP', 'Manual'
            $table->timestamps();
            $table->softDeletes();
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->text('deleted_by')->nullable();

            $table->index('single_role_id');
            $table->index('composite_role_id');
            $table->index('deleted_at');

            $table->unique(['composite_role_id', 'single_role_id'], 'unique_composite_single');

            $table->foreign('composite_role_id')->references('id')->on('tr_composite_roles')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('single_role_id')->references('id')->on('tr_single_roles')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pt_composite_role_single_role');
    }
};
