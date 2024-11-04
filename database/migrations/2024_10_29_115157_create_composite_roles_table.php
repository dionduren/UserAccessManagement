<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompositeRolesTable extends Migration
{
    public function up()
    {
        Schema::create('tr_composite_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('ms_company')->onDelete('set null');
            $table->string('nama'); // Composite Role Name
            $table->text('deskripsi')->nullable();
            $table->foreignId('jabatan_id')->nullable()->constrained('tr_job_roles')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->text('created_by')->nullable();
            $table->text('updated_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tr_composite_roles');
    }
}
