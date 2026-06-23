<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotSelectedReasonToWfhRegistrationsTable extends Migration
{
    public function up()
    {
        Schema::table('wfh_registrations', function (Blueprint $table) {
            $table->text('not_selected_reason')->nullable()->after('selected_at');
        });
    }

    public function down()
    {
        Schema::table('wfh_registrations', function (Blueprint $table) {
            $table->dropColumn('not_selected_reason');
        });
    }
}
