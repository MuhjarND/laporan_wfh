<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvidenFileColumnsToKegiatanWfhTable extends Migration
{
    public function up()
    {
        Schema::table('kegiatan_wfh', function (Blueprint $table) {
            $table->string('eviden_path')->nullable()->after('eviden_token');
            $table->string('eviden_original_name')->nullable()->after('eviden_path');
            $table->string('eviden_mime')->nullable()->after('eviden_original_name');
            $table->unsignedBigInteger('eviden_size')->nullable()->after('eviden_mime');
        });
    }

    public function down()
    {
        Schema::table('kegiatan_wfh', function (Blueprint $table) {
            $table->dropColumn([
                'eviden_path',
                'eviden_original_name',
                'eviden_mime',
                'eviden_size',
            ]);
        });
    }
}
