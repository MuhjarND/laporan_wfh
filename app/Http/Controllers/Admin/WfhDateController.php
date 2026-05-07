<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\WfhDate;
use App\User;
use App\LaporanWfh;
use App\Services\WhatsAppNotificationService;

class WfhDateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    public function index(Request $request)
    {
        $query = WfhDate::withCount('users');

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        $wfhDates = $query->orderBy('tanggal', 'desc')->paginate(20);

        return view('admin.wfh-dates.index', compact('wfhDates'));
    }

    public function create()
    {
        $users = User::whereIn('role', ['pegawai', 'atasan'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.wfh-dates.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string|max:255',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $selectedUserIds = User::whereIn('id', $request->user_ids)
            ->whereIn('role', ['pegawai', 'atasan'])
            ->pluck('id')
            ->all();

        if (count($selectedUserIds) !== count(array_unique($request->user_ids))) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Pilihan pegawai WFH tidak valid.');
        }

        $mulai = \Carbon\Carbon::parse($request->tanggal_mulai);
        $selesai = $request->tanggal_selesai
            ? \Carbon\Carbon::parse($request->tanggal_selesai)
            : $mulai->copy();

        $count = 0;
        while ($mulai->lte($selesai)) {
            $wfhDate = WfhDate::updateOrCreate(
                ['tanggal' => $mulai->toDateString()],
                ['keterangan' => $request->keterangan, 'is_active' => true]
            );
            $wfhDate->users()->sync($selectedUserIds);
            $mulai->addDay();
            $count++;
        }

        return redirect()->route('admin.wfh-dates.index')
            ->with('success', "{$count} tanggal WFH berhasil ditambahkan.");
    }

    public function edit(WfhDate $wfhDate)
    {
        $wfhDate->load('users');

        $users = User::whereIn('role', ['pegawai', 'atasan'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.wfh-dates.edit', compact('wfhDate', 'users'));
    }

    public function update(Request $request, WfhDate $wfhDate)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:wfh_dates,tanggal,' . $wfhDate->id,
            'keterangan' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $selectedUserIds = User::whereIn('id', $request->user_ids)
            ->whereIn('role', ['pegawai', 'atasan'])
            ->pluck('id')
            ->all();

        if (count($selectedUserIds) !== count(array_unique($request->user_ids))) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Pilihan pegawai WFH tidak valid.');
        }

        $wfhDate->update([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'is_active' => $request->boolean('is_active'),
        ]);
        $wfhDate->users()->sync($selectedUserIds);

        return redirect()->route('admin.wfh-dates.index')
            ->with('success', 'Tanggal WFH berhasil diperbarui.');
    }

    public function destroy(WfhDate $wfhDate)
    {
        $wfhDate->delete();

        return redirect()->route('admin.wfh-dates.index')
            ->with('success', 'Tanggal WFH berhasil dihapus.');
    }

    public function toggleActive(WfhDate $wfhDate)
    {
        $wfhDate->update(['is_active' => !$wfhDate->is_active]);

        return redirect()->route('admin.wfh-dates.index')
            ->with('success', 'Status tanggal WFH berhasil diperbarui.');
    }

    public function monitoring(Request $request)
    {
        $wfhDates = $this->monitoringQuery($request)->paginate(10);
        $reportStats = $this->reportStats($wfhDates->getCollection());

        return view('admin.wfh-dates.monitoring', compact('wfhDates', 'reportStats'));
    }

    public function sendReminder(WfhDate $wfhDate, WhatsAppNotificationService $whatsApp)
    {
        $wfhDate->load('users');
        $missingUsers = $this->missingActivityUsers($wfhDate);

        $sent = 0;
        $failed = 0;
        $withoutPhone = 0;

        foreach ($missingUsers as $user) {
            if (!$user->phone) {
                $withoutPhone++;
                continue;
            }

            if ($whatsApp->sendWfhReportReminder($user, $wfhDate)) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return redirect()->route('admin.wfh-dates.monitoring')
            ->with('success', 'Notifikasi isi kegiatan tanggal ' . $wfhDate->tanggal->format('d/m/Y') . ' diproses. Terkirim: ' . $sent . ', tanpa nomor WA: ' . $withoutPhone . ', gagal: ' . $failed . '.');
    }

    public function sendSubmitReminder(WfhDate $wfhDate, WhatsAppNotificationService $whatsApp)
    {
        $wfhDate->load('users');
        $unsubmittedUsers = $this->unsubmittedReportUsers($wfhDate);

        $sent = 0;
        $failed = 0;
        $withoutPhone = 0;

        foreach ($unsubmittedUsers as $user) {
            if (!$user->phone) {
                $withoutPhone++;
                continue;
            }

            if ($whatsApp->sendWfhSubmitReminder($user, $wfhDate)) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return redirect()->route('admin.wfh-dates.monitoring')
            ->with('success', 'Notifikasi pengiriman laporan tanggal ' . $wfhDate->tanggal->format('d/m/Y') . ' diproses. Terkirim: ' . $sent . ', tanpa nomor WA: ' . $withoutPhone . ', gagal: ' . $failed . '.');
    }

    public function sendAllActivityReminders(Request $request, WhatsAppNotificationService $whatsApp)
    {
        $wfhDates = $this->monitoringQuery($request)->get();
        $summary = $this->sendReminderToUsers($wfhDates, $whatsApp, 'activity');

        return redirect()->route('admin.wfh-dates.monitoring', $request->only(['tanggal', 'bulan', 'tahun']))
            ->with('success', 'Notifikasi isi kegiatan diproses. Tanggal: ' . $summary['dates'] . ', terkirim: ' . $summary['sent'] . ', tanpa nomor WA: ' . $summary['withoutPhone'] . ', gagal: ' . $summary['failed'] . '.');
    }

    public function sendAllSubmitReminders(Request $request, WhatsAppNotificationService $whatsApp)
    {
        $wfhDates = $this->monitoringQuery($request)->get();
        $summary = $this->sendReminderToUsers($wfhDates, $whatsApp, 'submit');

        return redirect()->route('admin.wfh-dates.monitoring', $request->only(['tanggal', 'bulan', 'tahun']))
            ->with('success', 'Notifikasi pengiriman laporan diproses. Tanggal: ' . $summary['dates'] . ', terkirim: ' . $summary['sent'] . ', tanpa nomor WA: ' . $summary['withoutPhone'] . ', gagal: ' . $summary['failed'] . '.');
    }

    public function missingActivityUsers(WfhDate $wfhDate)
    {
        $assignedUsers = $this->assignedUsersForDate($wfhDate);
        $reportedUserIds = $this->reportedUserIds($wfhDate);

        return $assignedUsers->whereNotIn('id', $reportedUserIds)->values();
    }

    public function unsubmittedReportUsers(WfhDate $wfhDate)
    {
        $assignedUsers = $this->assignedUsersForDate($wfhDate);

        $unsubmittedUserIds = LaporanWfh::where('bulan', $wfhDate->tanggal->month)
            ->where('tahun', $wfhDate->tanggal->year)
            ->whereNotIn('status', ['submitted', 'approved'])
            ->whereHas('kegiatan', function ($query) use ($wfhDate) {
                $query->whereDate('tanggal', $wfhDate->tanggal->toDateString());
            })
            ->pluck('user_id')
            ->unique();

        return $assignedUsers->whereIn('id', $unsubmittedUserIds)->values();
    }

    private function reportStats($wfhDates)
    {
        $stats = [];

        foreach ($wfhDates as $wfhDate) {
            $assignedUsers = $this->assignedUsersForDate($wfhDate);
            $reportedUserIds = $this->reportedUserIds($wfhDate);

            $reportedUsers = $assignedUsers->whereIn('id', $reportedUserIds)->values();
            $missingUsers = $assignedUsers->whereNotIn('id', $reportedUserIds)->values();
            $unsubmittedUsers = $this->unsubmittedReportUsers($wfhDate);

            $stats[$wfhDate->id] = [
                'assigned' => $assignedUsers,
                'reported' => $reportedUsers,
                'missing' => $missingUsers,
                'unsubmitted' => $unsubmittedUsers,
            ];
        }

        return $stats;
    }

    private function monitoringQuery(Request $request)
    {
        $query = WfhDate::with(['users' => function ($q) {
            $q->orderBy('name');
        }])->where('is_active', true);

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        return $query->orderBy('tanggal', 'desc');
    }

    private function sendReminderToUsers($wfhDates, WhatsAppNotificationService $whatsApp, $type)
    {
        $sent = 0;
        $failed = 0;
        $withoutPhone = 0;
        $dates = 0;
        $targets = [];

        foreach ($wfhDates as $wfhDate) {
            $dates++;
            $users = $type === 'submit'
                ? $this->unsubmittedReportUsers($wfhDate)
                : $this->missingActivityUsers($wfhDate);

            foreach ($users as $user) {
                $key = $wfhDate->id . '-' . $user->id;
                if (isset($targets[$key])) {
                    continue;
                }
                $targets[$key] = true;

                if (!$user->phone) {
                    $withoutPhone++;
                    continue;
                }

                $ok = $type === 'submit'
                    ? $whatsApp->sendWfhSubmitReminder($user, $wfhDate)
                    : $whatsApp->sendWfhReportReminder($user, $wfhDate);

                if ($ok) {
                    $sent++;
                } else {
                    $failed++;
                }
            }
        }

        return compact('dates', 'sent', 'failed', 'withoutPhone');
    }

    private function assignedUsersForDate(WfhDate $wfhDate)
    {
        if ($wfhDate->relationLoaded('users') && $wfhDate->users->isNotEmpty()) {
            return $wfhDate->users;
        }

        if (!$wfhDate->relationLoaded('users')) {
            $wfhDate->load('users');
        }

        if ($wfhDate->users->isNotEmpty()) {
            return $wfhDate->users;
        }

        return User::whereIn('role', ['pegawai', 'atasan'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function reportedUserIds(WfhDate $wfhDate)
    {
        return LaporanWfh::where('bulan', $wfhDate->tanggal->month)
            ->where('tahun', $wfhDate->tanggal->year)
            ->whereHas('kegiatan', function ($query) use ($wfhDate) {
                $query->whereDate('tanggal', $wfhDate->tanggal->toDateString());
            })
            ->pluck('user_id')
            ->unique();
    }
}
