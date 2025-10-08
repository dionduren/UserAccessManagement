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
        Schema::create('ms_non_sap_uid', function (Blueprint $table) {
            $table->id();
            $table->string('user_code');
            $table->string('user_type')->nullable();
            $table->string('user_profile')->nullable();
            $table->string('nik')->nullable();
            $table->string('cost_code')->nullable(); //mungkin perlu dihapus
            $table->string('license_type')->nullable();
            $table->string('group')->nullable(); // nempel ke shortname ms_company
            // new column start
            $table->foreignId('periode_id')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->string('keterangan')->nullable();
            $table->boolean('uar_listed')->default(false);
            $table->string('error_kompartemen_id')->nullable();
            $table->string('error_departemen_id')->nullable();
            $table->boolean('flagged')->default(false);
            $table->text('keterangan_flagged')->nullable();
            // new column end
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_non_sap_uid');
    }
};
