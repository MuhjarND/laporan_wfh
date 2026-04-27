<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvidenToKegiatanWfhTable extends Migration
{
    public function up()
    {
        Schema::table('kegiatan_wfh', function (Blueprint $table) {
            $table->text('eviden_url')->nullable()->after('tempat_wfh');
            $table->uuid('eviden_token')->nullable()->unique()->after('eviden_url');
        });
    }

    public function down()
    {
        Schema::table('kegiatan_wfh', function (Blueprint $table) {
            $table->dropUnique(['eviden_token']);
            $table->dropColumn(['eviden_url', 'eviden_token']);
        });
    }
}
