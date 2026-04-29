<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateKegiatanWfhEvidensTable extends Migration
{
    public function up()
    {
        Schema::create('kegiatan_wfh_evidens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kegiatan_id');
            $table->uuid('token')->unique();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();

            $table->foreign('kegiatan_id')->references('id')->on('kegiatan_wfh')->onDelete('cascade');
        });

        DB::table('kegiatan_wfh')
            ->whereNotNull('eviden_path')
            ->whereNotNull('eviden_token')
            ->orderBy('id')
            ->chunk(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('kegiatan_wfh_evidens')->insert([
                        'kegiatan_id' => $row->id,
                        'token' => $row->eviden_token,
                        'path' => $row->eviden_path,
                        'original_name' => $row->eviden_original_name,
                        'mime' => $row->eviden_mime,
                        'size' => $row->eviden_size,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ]);
                }
            });
    }

    public function down()
    {
        Schema::dropIfExists('kegiatan_wfh_evidens');
    }
}
