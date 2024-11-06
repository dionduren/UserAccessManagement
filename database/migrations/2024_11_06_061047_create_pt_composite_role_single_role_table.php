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
            $table->foreignId('composite_role_id')->constrained('tr_composite_roles')->onDelete('cascade');
            $table->foreignId('single_role_id')->constrained('tr_single_roles')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps(); // This will add created_at and updated_at columns
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->boolean('is_deleted')->default(0);
            $table->text('deleted_by')->nullable();
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
