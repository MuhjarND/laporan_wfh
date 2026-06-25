<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SyncLegacyWfhRegistrations extends Migration
{
    public function up()
    {
        DB::table('wfh_date_user')
            ->orderBy('id')
            ->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    $exists = DB::table('wfh_registrations')
                        ->where('wfh_date_id', $row->wfh_date_id)
                        ->where('user_id', $row->user_id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    DB::table('wfh_registrations')->insert([
                        'wfh_date_id' => $row->wfh_date_id,
                        'user_id' => $row->user_id,
                        'status' => 'selected',
                        'selected_at' => $row->created_at ?: now(),
                        'not_selected_reason' => null,
                        'created_at' => $row->created_at ?: now(),
                        'updated_at' => $row->updated_at ?: now(),
                    ]);
                }
            });
    }

    public function down()
    {
        // Data hasil sinkronisasi tidak dihapus agar riwayat pendaftaran tetap aman.
    }
}
