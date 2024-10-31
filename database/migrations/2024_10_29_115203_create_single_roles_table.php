<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSingleRolesTable extends Migration
{
    public function up()
    {
        Schema::create('single_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->string('nama'); // Single Role Name
            $table->text('deskripsi')->nullable();
            $table->foreignId('composite_role_id')->nullable()->constrained('composite_roles')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('single_roles');
    }
}
