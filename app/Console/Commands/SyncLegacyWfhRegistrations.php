<?php

namespace App\Console\Commands;

use App\WfhRegistration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncLegacyWfhRegistrations extends Command
{
    protected $signature = 'wfh:sync-legacy-registrations';

    protected $description = 'Sinkronkan data peserta WFH lama dari pivot wfh_date_user ke tabel wfh_registrations.';

    public function handle()
    {
        $created = $this->syncLegacyRegistrations();

        $this->info('Sinkronisasi data lama selesai. Pendaftaran terpilih dibuat: ' . $created . '.');

        return 0;
    }

    private function syncLegacyRegistrations()
    {
        $created = 0;

        DB::table('wfh_date_user')
            ->orderBy('id')
            ->chunk(200, function ($rows) use (&$created) {
                foreach ($rows as $row) {
                    $exists = WfhRegistration::where('wfh_date_id', $row->wfh_date_id)
                        ->where('user_id', $row->user_id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    WfhRegistration::create([
                        'wfh_date_id' => $row->wfh_date_id,
                        'user_id' => $row->user_id,
                        'status' => 'selected',
                        'selected_at' => $row->created_at ?: now(),
                        'not_selected_reason' => null,
                    ]);

                    $created++;
                }
            });

        return $created;
    }
}
