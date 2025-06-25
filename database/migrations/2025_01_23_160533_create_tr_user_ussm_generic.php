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
        Schema::create('tr_user_generic', function (Blueprint $table) {
            $table->id();
            $table->string('user_code');
            $table->string('user_type');
            $table->string('cost_code')->nullable(); //mungkin perlu dihapus
            $table->string('license_type');
            $table->string('group')->nullable(); // nempel ke shortname ms_company
            // new column start
            $table->string('pic')->nullable();
            $table->string('unit_kerja')->nullable();
            $table->string('kompartemen_id')->nullable();
            $table->string('departemen_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('error_kompartemen_id')->nullable();
            $table->string('error_departemen_id')->nullable();
            $table->string('flagged')->nullable();
            $table->string('keterangan_flagged')->nullable();
            $table->foreignId('periode_id')->nullable();
            $table->dateTime('last_login')->nullable();
            // new column end
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('tr_user_ussm_generic');
        Schema::dropIfExists('tr_user_generic');
    }
};
