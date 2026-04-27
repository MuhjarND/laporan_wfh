<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSignaturesToLaporanWfhTable extends Migration
{
    public function up()
    {
        Schema::table('laporan_wfh', function (Blueprint $table) {
            $table->longText('signature_pegawai')->nullable()->after('catatan_atasan');
            $table->longText('signature_atasan')->nullable()->after('signature_pegawai');
        });
    }

    public function down()
    {
        Schema::table('laporan_wfh', function (Blueprint $table) {
            $table->dropColumn(['signature_pegawai', 'signature_atasan']);
        });
    }
}
