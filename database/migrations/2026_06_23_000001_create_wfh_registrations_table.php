<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWfhRegistrationsTable extends Migration
{
    public function up()
    {
        Schema::create('wfh_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wfh_date_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('status', ['registered', 'selected', 'not_selected'])->default('registered');
            $table->timestamp('selected_at')->nullable();
            $table->timestamps();

            $table->foreign('wfh_date_id')->references('id')->on('wfh_dates')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['wfh_date_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('wfh_registrations');
    }
}
