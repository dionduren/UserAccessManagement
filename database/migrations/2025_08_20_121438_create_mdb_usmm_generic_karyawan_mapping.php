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
        Schema::create('mdb_usmm_generic_karyawan_mapping', function (Blueprint $table) {
            $table->id();
            $table->string('sap_user_id')->index();        // ua.bname
            $table->string('user_full_name')->nullable();  // ua.name_textc
            $table->string('company')->nullable();         // k.company
            $table->string('personnel_number')->nullable(); // k.emp_no
            $table->string('employee_full_name')->nullable(); // k.nama
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mdb_usmm_generic_karyawan_mapping');
    }
};
