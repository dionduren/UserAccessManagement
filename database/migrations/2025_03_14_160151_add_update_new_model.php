<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('tr_job_roles', function (Blueprint $table) {
            $table->string('status')->nullable()->after('nama_jabatan'); // Sesuaikan posisi sesuai kebutuhan
        });

        Schema::table('ms_user_detail', function (Blueprint $table) {
            $table->foreignId('periode_id')->nullable()->after('cost_center');
        });

        Schema::table('tr_user_ussm_nik', function (Blueprint $table) {
            $table->foreignId('periode_id')->nullable()->after('group');
            $table->dateTime('last_login')->nullable()->after('periode_id');
        });
    }

    public function down()
    {
        Schema::table('tr_job_roles', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('ms_user_detail', function (Blueprint $table) {
            $table->dropColumn('periode_id');
        });

        Schema::table('tr_user_ussm_nik', function (Blueprint $table) {
            $table->dropColumn(['periode_id', 'last_login']);
        });
    }
};
