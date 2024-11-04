<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSingleRolesTable extends Migration
{
    public function up()
    {
        Schema::create('tr_single_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('ms_company')->onDelete('set null');
            $table->foreignId('composite_role_id')->nullable()->constrained('tr_composite_roles')->onDelete('set null');
            $table->string('nama'); // Single Role Name
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
            $table->boolean('is_deleted')->default(0);
            $table->text('deleted_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tr_single_roles');
    }
}
