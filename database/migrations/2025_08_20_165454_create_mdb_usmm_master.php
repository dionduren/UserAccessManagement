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
        Schema::create('mdb_usmm_master', function (Blueprint $table) {
            $table->id();
            $table->string('company')->nullable();           // ll.class
            $table->string('sap_user_id')->index();          // ua.bname
            $table->string('full_name')->nullable();         // ua.name_textc
            $table->string('department')->nullable();        // ua.department
            $table->string('last_logon_date')->nullable();     // ll.trdat
            $table->string('last_logon_time')->nullable();     // ll.ltime
            $table->string('user_type')->nullable();           // ll.ustyp (A/B/…)
            $table->string('user_type_desc')->nullable();     // mapped desc
            $table->string('valid_from')->nullable();          // ll.gltgv (YYYYMMDD)
            $table->string('valid_to')->nullable();            // ll.gltgb (YYYYMMDD)
            $table->string('contractual_user_type')->nullable();      // us06.lic_type (CA/CB/…)
            $table->string('contr_user_type_desc')->nullable();     // mapped desc
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mdb_usmm_master');
    }
};
