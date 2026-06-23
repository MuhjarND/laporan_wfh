<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLetterApprovalFieldsToWfhDatesTable extends Migration
{
    public function up()
    {
        Schema::table('wfh_dates', function (Blueprint $table) {
            $table->string('letter_status')->default('draft')->after('letter_number');
            $table->timestamp('letter_requested_at')->nullable()->after('letter_status');
            $table->timestamp('letter_approved_at')->nullable()->after('letter_requested_at');
            $table->unsignedBigInteger('letter_approved_by')->nullable()->after('letter_approved_at');
            $table->longText('letter_signature')->nullable()->after('letter_approved_by');

            $table->foreign('letter_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('wfh_dates', function (Blueprint $table) {
            $table->dropForeign(['letter_approved_by']);
            $table->dropColumn([
                'letter_status',
                'letter_requested_at',
                'letter_approved_at',
                'letter_approved_by',
                'letter_signature',
            ]);
        });
    }
}
