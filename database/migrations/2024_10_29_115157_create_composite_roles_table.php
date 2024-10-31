<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompositeRolesTable extends Migration
{
    public function up()
    {
        Schema::create('composite_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->string('nama'); // Composite Role Name
            $table->text('deskripsi')->nullable();
            $table->foreignId('jabatan_id')->nullable()->constrained('job_roles')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('composite_roles');
    }
}
