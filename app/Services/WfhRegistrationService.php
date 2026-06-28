<?php

namespace App\Services;

use App\LaporanWfh;
use App\User;
use App\WfhDate;
use App\WfhRegistration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WfhRegistrationService
{
    public function quota(): int
    {
        $eligibleUsers = User::whereIn('role', ['pegawai', 'atasan'])
            ->where('is_active', true)
            ->count();

        return max(1, (int) floor($eligibleUsers * 0.5));
    }

    public function selectedCount(WfhDate $wfhDate): int
    {
        return $wfhDate->users()->count();
    }

    public function remainingQuota(WfhDate $wfhDate): int
    {
        return max(0, $this->quota() - $this->selectedCount($wfhDate));
    }

    public function registrationClosesAt(WfhDate $wfhDate): Carbon
    {
        return Carbon::parse($wfhDate->tanggal->format('Y-m-d'), 'Asia/Jayapura')
            ->subDay()
            ->setTime(16, 30);
    }

    public function isRegistrationOpen(WfhDate $wfhDate): bool
    {
        return now('Asia/Jayapura')->lte($this->registrationClosesAt($wfhDate));
    }

    public function registrationClosedMessage(WfhDate $wfhDate): string
    {
        return 'Pendaftaran WFH telah ditutup pada ' . $this->registrationClosesAt($wfhDate)->format('d/m/Y H:i') . ' WIT.';
    }

    public function canRegister(User $user, WfhDate $wfhDate): array
    {
        if (!in_array($user->role, ['pegawai', 'atasan'], true) || !$user->is_active) {
            return [false, 'Akun Anda tidak dapat melakukan pendaftaran WFH.'];
        }

        if (!$wfhDate->is_active) {
            return [false, 'Tanggal WFH ini tidak aktif.'];
        }

        if (in_array($wfhDate->letter_status, ['pending_approval', 'approved'], true)) {
            return [false, 'Pendaftaran WFH tanggal ini sudah masuk proses surat tugas dan tidak dapat diubah.'];
        }

        if ($wfhDate->tanggal->isPast() && !$wfhDate->tanggal->isToday()) {
            return [false, 'Tidak dapat mendaftar untuk tanggal WFH yang sudah lewat.'];
        }

        if (!$this->isRegistrationOpen($wfhDate)) {
            return [false, $this->registrationClosedMessage($wfhDate)];
        }

        if ($wfhDate->registrations()->where('user_id', $user->id)->exists()) {
            return [false, 'Anda sudah terdaftar pada tanggal WFH ini.'];
        }

        $incompleteDate = $this->incompletePriorAssignedWfhDate($user, $wfhDate);
        if ($incompleteDate) {
            return [false, 'Anda masih memiliki laporan kegiatan WFH yang belum selesai pada tanggal ' . $incompleteDate->tanggal->format('d/m/Y') . '. Silakan lengkapi kegiatan terlebih dahulu.'];
        }

        return [true, null];
    }

    public function register(User $user, WfhDate $wfhDate): WfhRegistration
    {
        return DB::transaction(function () use ($user, $wfhDate) {
            $registration = WfhRegistration::firstOrCreate([
                'wfh_date_id' => $wfhDate->id,
                'user_id' => $user->id,
            ], [
                'status' => 'registered',
            ]);

            $this->recalculateSelection($wfhDate->fresh());

            return $registration->fresh();
        });
    }

    public function canCancel(User $user, WfhDate $wfhDate): array
    {
        if (!$wfhDate->registrations()->where('user_id', $user->id)->exists()) {
            return [false, 'Anda belum terdaftar pada tanggal WFH ini.'];
        }

        if (in_array($wfhDate->letter_status, ['pending_approval', 'approved'], true)) {
            return [false, 'Pendaftaran WFH tidak dapat dibatalkan karena sudah masuk proses surat tugas.'];
        }

        if (!$this->isRegistrationOpen($wfhDate)) {
            return [false, $this->registrationClosedMessage($wfhDate) . ' Pembatalan pendaftaran sudah tidak dapat dilakukan.'];
        }

        return [true, null];
    }

    public function cancel(User $user, WfhDate $wfhDate)
    {
        return DB::transaction(function () use ($user, $wfhDate) {
            $this->ensureRegistrationsFromAssignedUsers($wfhDate);

            $beforeSelectedIds = WfhRegistration::where('wfh_date_id', $wfhDate->id)
                ->where('status', 'selected')
                ->pluck('user_id')
                ->all();

            WfhRegistration::where('wfh_date_id', $wfhDate->id)
                ->where('user_id', $user->id)
                ->delete();

            DB::table('wfh_date_user')
                ->where('wfh_date_id', $wfhDate->id)
                ->where('user_id', $user->id)
                ->delete();

            $this->recalculateSelection($wfhDate->fresh());

            $afterSelectedIds = WfhRegistration::where('wfh_date_id', $wfhDate->id)
                ->where('status', 'selected')
                ->pluck('user_id')
                ->all();

            $replacementIds = array_values(array_diff($afterSelectedIds, $beforeSelectedIds));

            if (empty($replacementIds)) {
                return collect();
            }

            return WfhRegistration::with('user')
                ->where('wfh_date_id', $wfhDate->id)
                ->whereIn('user_id', $replacementIds)
                ->get();
        });
    }

    public function recalculateSelection(WfhDate $wfhDate): void
    {
        $this->ensureRegistrationsFromAssignedUsers($wfhDate);

        $quota = $this->quota();
        $registrations = WfhRegistration::with('user')
            ->where('wfh_date_id', $wfhDate->id)
            ->get();

        $ranked = $registrations->sortBy(function ($registration) use ($wfhDate) {
            return sprintf(
                '%d-%010d-%010d-%010d',
                $this->hadWfhOnPreviousFriday($registration->user, $wfhDate) ? 1 : 0,
                $this->totalWfhCount($registration->user, $wfhDate),
                optional($registration->created_at)->timestamp ?: 0,
                $registration->id
            );
        })->values();

        $selectedIds = $ranked->take($quota)->pluck('user_id')->all();
        $notSelectedReason = 'Tidak terpilih karena kuota WFH tanggal ini sudah terpenuhi. Sistem memprioritaskan pegawai yang belum WFH Jumat minggu sebelumnya dan pegawai dengan total WFH lebih sedikit.';

        WfhRegistration::where('wfh_date_id', $wfhDate->id)->update([
            'status' => 'not_selected',
            'selected_at' => null,
            'not_selected_reason' => $notSelectedReason,
        ]);

        if (!empty($selectedIds)) {
            WfhRegistration::where('wfh_date_id', $wfhDate->id)
                ->whereIn('user_id', $selectedIds)
                ->update([
                    'status' => 'selected',
                    'selected_at' => now(),
                    'not_selected_reason' => null,
                ]);
        }

        $wfhDate->users()->sync($selectedIds);
    }

    public function hasCompletedPreviousWeekActivities(User $user, WfhDate $wfhDate): bool
    {
        return $this->incompletePriorAssignedWfhDate($user, $wfhDate) === null;
    }

    public function incompletePriorAssignedWfhDate(User $user, WfhDate $wfhDate)
    {
        $previousDates = WfhDate::where('is_active', true)
            ->whereDate('tanggal', '<', $wfhDate->tanggal->toDateString())
            ->where(function ($query) use ($user) {
                $query->whereHas('users', function ($subQuery) use ($user) {
                    $subQuery->where('users.id', $user->id);
                })->orWhereHas('registrations', function ($subQuery) use ($user) {
                    $subQuery->where('user_id', $user->id)
                        ->where('status', 'selected');
                });
            })
            ->orderBy('tanggal')
            ->get();

        if ($previousDates->isEmpty()) {
            return null;
        }

        foreach ($previousDates as $date) {
            if (!$this->hasActivityForWfhDate($user, $date)) {
                return $date;
            }
        }

        return null;
    }

    private function hasActivityForWfhDate(User $user, WfhDate $wfhDate): bool
    {
        $exactDate = $wfhDate->tanggal->toDateString();
        $weekStart = $wfhDate->tanggal->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $weekEnd = $wfhDate->tanggal->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();

        $hasExactActivity = LaporanWfh::where('user_id', $user->id)
            ->whereHas('kegiatan', function ($query) use ($exactDate) {
                $query->whereDate('tanggal', $exactDate);
            })
            ->exists();

        if ($hasExactActivity) {
            return true;
        }

        return LaporanWfh::where('user_id', $user->id)
            ->whereHas('kegiatan', function ($query) use ($weekStart, $weekEnd) {
                $query->whereBetween('tanggal', [$weekStart, $weekEnd]);
            })
            ->exists();
    }

    private function hadWfhOnPreviousFriday(User $user, WfhDate $wfhDate): bool
    {
        $previousFriday = $wfhDate->tanggal->copy()->previous(Carbon::FRIDAY);

        return WfhDate::whereDate('tanggal', $previousFriday->toDateString())
            ->where(function ($query) use ($user) {
                $query->whereHas('users', function ($subQuery) use ($user) {
                    $subQuery->where('users.id', $user->id);
                })->orWhereHas('registrations', function ($subQuery) use ($user) {
                    $subQuery->where('user_id', $user->id)
                        ->where('status', 'selected');
                });
            })
            ->exists();
    }

    private function totalWfhCount(User $user, WfhDate $currentDate): int
    {
        $pivotIds = DB::table('wfh_date_user')
            ->where('user_id', $user->id)
            ->where('wfh_date_id', '!=', $currentDate->id)
            ->pluck('wfh_date_id');

        $registrationIds = WfhRegistration::where('user_id', $user->id)
            ->where('wfh_date_id', '!=', $currentDate->id)
            ->where('status', 'selected')
            ->pluck('wfh_date_id');

        return $pivotIds->merge($registrationIds)->unique()->count();
    }

    private function ensureRegistrationsFromAssignedUsers(WfhDate $wfhDate): void
    {
        $assignedUserIds = DB::table('wfh_date_user')
            ->where('wfh_date_id', $wfhDate->id)
            ->pluck('user_id');

        foreach ($assignedUserIds as $userId) {
            WfhRegistration::firstOrCreate([
                'wfh_date_id' => $wfhDate->id,
                'user_id' => $userId,
            ], [
                'status' => 'selected',
                'selected_at' => now(),
                'not_selected_reason' => null,
            ]);
        }
    }
}
