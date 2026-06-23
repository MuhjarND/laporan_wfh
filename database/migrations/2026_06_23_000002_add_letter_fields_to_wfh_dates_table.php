<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLetterFieldsToWfhDatesTable extends Migration
{
    public function up()
    {
        Schema::table('wfh_dates', function (Blueprint $table) {
            $table->string('letter_number')->nullable()->after('keterangan');
            $table->timestamp('letter_published_at')->nullable()->after('letter_number');
            $table->timestamp('letter_notified_at')->nullable()->after('letter_published_at');
        });
    }

    public function down()
    {
        Schema::table('wfh_dates', function (Blueprint $table) {
            $table->dropColumn(['letter_number', 'letter_published_at', 'letter_notified_at']);
        });
    }
}
