<?php

namespace App\Console\Commands;

use App\KegiatanWfh;
use App\LaporanWfh;
use App\Services\WfhRegistrationService;
use App\User;
use App\WfhDate;
use App\WfhRegistration;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeWfhDummyData extends Command
{
    protected $signature = 'wfh:dummy-registrations';

    protected $description = 'Membuat data dummy pendaftaran WFH minggu sebelumnya dan minggu berjalan untuk testing.';

    public function handle(WfhRegistrationService $registrationService)
    {
        DB::transaction(function () use ($registrationService) {
            $users = User::whereIn('role', ['pegawai', 'atasan'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            if ($users->count() < 4) {
                $this->error('Minimal butuh 4 user aktif role pegawai/atasan. Saat ini: ' . $users->count());
                return;
            }

            $currentFriday = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays(4)->startOfDay();
            $previousFriday = $currentFriday->copy()->subWeek();

            $previousDate = WfhDate::updateOrCreate(
                ['tanggal' => $previousFriday->toDateString()],
                ['keterangan' => 'Dummy Testing - WFH Jumat Minggu Sebelumnya', 'is_active' => true]
            );

            $currentDate = WfhDate::updateOrCreate(
                ['tanggal' => $currentFriday->toDateString()],
                ['keterangan' => 'Dummy Testing - WFH Jumat Minggu Berjalan', 'is_active' => true]
            );

            $quota = $registrationService->quota();
            $previousUsers = $users->take(min(6, $users->count()))->values();
            $currentUsers = $users->take(min($users->count(), max(6, $quota + 2)))->values();

            foreach ($previousUsers as $index => $user) {
                WfhRegistration::updateOrCreate(
                    ['wfh_date_id' => $previousDate->id, 'user_id' => $user->id],
                    ['status' => 'selected', 'selected_at' => $previousFriday->copy()->setTime(8, 0)->addMinutes($index)]
                );
            }
            $previousDate->users()->sync($previousUsers->pluck('id')->all());

            $usersWithPreviousActivity = $previousUsers->take(max(1, $previousUsers->count() - 1));
            foreach ($usersWithPreviousActivity as $user) {
                $laporan = LaporanWfh::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'bulan' => (int) $previousFriday->format('n'),
                        'tahun' => (int) $previousFriday->format('Y'),
                    ],
                    ['status' => 'draft']
                );

                KegiatanWfh::updateOrCreate(
                    ['laporan_id' => $laporan->id, 'tanggal' => $previousFriday->toDateString()],
                    [
                        'kegiatan' => '<p>Dummy testing kegiatan WFH minggu sebelumnya untuk ' . e($user->name) . '.</p>',
                        'capaian' => '<p>Dummy testing capaian pekerjaan selesai dan terdokumentasi.</p>',
                        'tempat_wfh' => 'Rumah',
                    ]
                );
            }

            foreach ($currentUsers as $user) {
                WfhRegistration::updateOrCreate(
                    ['wfh_date_id' => $currentDate->id, 'user_id' => $user->id],
                    ['status' => 'registered', 'selected_at' => null]
                );
            }
            $registrationService->recalculateSelection($currentDate->fresh());

            $currentDate = $currentDate->fresh(['registrations.user', 'users']);
            $missingPrevious = $previousUsers->last();

            $this->info('Dummy testing WFH berhasil dibuat.');
            $this->line('Tanggal minggu sebelumnya: ' . $previousFriday->toDateString());
            $this->line('Tanggal minggu berjalan: ' . $currentFriday->toDateString());
            $this->line('Kuota sistem: ' . $quota);
            $this->line('Pendaftar minggu sebelumnya: ' . $previousUsers->count() . ' (semua selected)');
            $this->line('Peserta minggu sebelumnya yang sudah punya kegiatan: ' . $usersWithPreviousActivity->count());
            $this->line('Peserta sengaja tanpa kegiatan minggu sebelumnya: ' . ($missingPrevious ? $missingPrevious->name . ' / ' . $missingPrevious->nip : '-'));
            $this->line('Pendaftar minggu berjalan: ' . $currentDate->registrations->count());
            $this->line('Terpilih minggu berjalan: ' . $currentDate->registrations->where('status', 'selected')->count());
            $this->line('Tidak terpilih minggu berjalan: ' . $currentDate->registrations->where('status', 'not_selected')->count());
        });

        return 0;
    }
}
