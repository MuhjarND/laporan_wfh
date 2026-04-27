<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKegiatanWfhTable extends Migration
{
    public function up()
    {
        Schema::create('kegiatan_wfh', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laporan_id');
            $table->date('tanggal');
            $table->text('kegiatan');
            $table->text('capaian');
            $table->string('tempat_wfh');
            $table->timestamps();
            $table->foreign('laporan_id')->references('id')->on('laporan_wfh')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kegiatan_wfh');
    }
}
