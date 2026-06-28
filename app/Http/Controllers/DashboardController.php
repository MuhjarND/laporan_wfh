<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\LaporanWfh;
use App\KegiatanWfh;
use App\WfhDate;
use App\WfhRegistration;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return $this->adminDashboard();
        } elseif ($user->isAtasan()) {
            return $this->atasanDashboard();
        } else {
            return $this->pegawaiDashboard();
        }
    }

    private function adminDashboard()
    {
        $totalUsers = User::count();
        $totalPegawai = User::where('role', 'pegawai')->count();
        $totalAtasan = User::where('role', 'atasan')->count();
        $totalLaporan = LaporanWfh::count();
        $laporanSubmitted = LaporanWfh::where('status', 'submitted')->count();
        $laporanApproved = LaporanWfh::where('status', 'approved')->count();
        $totalWfhDates = WfhDate::where('is_active', true)->count();
        $recentKegiatan = KegiatanWfh::with('laporan.user')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.admin', compact(
            'totalUsers', 'totalPegawai', 'totalAtasan',
            'totalLaporan', 'laporanSubmitted', 'laporanApproved',
            'totalWfhDates', 'recentKegiatan'
        ));
    }

    private function atasanDashboard()
    {
        $user = auth()->user();
        $bawahan = User::where('atasan_id', $user->id)->get();
        $totalBawahan = $bawahan->count();

        $bawahanIds = $bawahan->pluck('id');
        $laporanSubmitted = LaporanWfh::whereIn('user_id', $bawahanIds)
            ->where('status', 'submitted')
            ->count();
        $laporanApproved = LaporanWfh::whereIn('user_id', $bawahanIds)
            ->where('status', 'approved')
            ->count();
        $totalLaporan = LaporanWfh::whereIn('user_id', $bawahanIds)->count();

        $recentKegiatan = KegiatanWfh::with('laporan.user')
            ->whereHas('laporan', function ($query) use ($bawahanIds) {
                $query->whereIn('user_id', $bawahanIds);
            })
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        $laporanSayaTotal = LaporanWfh::where('user_id', $user->id)->count();
        $laporanSayaDraft = LaporanWfh::where('user_id', $user->id)->where('status', 'draft')->count();
        $laporanSayaSubmitted = LaporanWfh::where('user_id', $user->id)->where('status', 'submitted')->count();
        $laporanSayaApproved = LaporanWfh::where('user_id', $user->id)->where('status', 'approved')->count();

        $currentLaporan = LaporanWfh::where('user_id', $user->id)
            ->where('bulan', now()->month)
            ->where('tahun', now()->year)
            ->first();

        $recentKegiatanSaya = KegiatanWfh::with('laporan')
            ->whereHas('laporan', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();
        $dashboardWfhInfo = $this->dashboardWfhInfo($user);

        return view('dashboard.atasan', compact(
            'totalBawahan', 'laporanSubmitted', 'laporanApproved',
            'totalLaporan', 'recentKegiatan', 'bawahan',
            'laporanSayaTotal', 'laporanSayaDraft', 'laporanSayaSubmitted',
            'laporanSayaApproved', 'currentLaporan', 'recentKegiatanSaya',
            'dashboardWfhInfo'
        ));
    }

    private function pegawaiDashboard()
    {
        $user = auth()->user();
        $totalLaporan = LaporanWfh::where('user_id', $user->id)->count();
        $laporanDraft = LaporanWfh::where('user_id', $user->id)->where('status', 'draft')->count();
        $laporanSubmitted = LaporanWfh::where('user_id', $user->id)->where('status', 'submitted')->count();
        $laporanApproved = LaporanWfh::where('user_id', $user->id)->where('status', 'approved')->count();

        $recentKegiatan = KegiatanWfh::with('laporan')
            ->whereHas('laporan', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // Check if current month report exists
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $currentLaporan = LaporanWfh::where('user_id', $user->id)
            ->where('bulan', $currentMonth)
            ->where('tahun', $currentYear)
            ->first();
        $dashboardWfhInfo = $this->dashboardWfhInfo($user);

        return view('dashboard.pegawai', compact(
            'totalLaporan', 'laporanDraft', 'laporanSubmitted',
            'laporanApproved', 'recentKegiatan', 'currentLaporan',
            'dashboardWfhInfo'
        ));
    }

    private function dashboardWfhInfo(User $user)
    {
        $today = now()->toDateString();

        $registrations = WfhRegistration::with('wfhDate')
            ->where('user_id', $user->id)
            ->whereHas('wfhDate', function ($query) use ($today) {
                $query->where('is_active', true)
                    ->whereDate('tanggal', '>=', $today);
            })
            ->get()
            ->sortBy(function ($registration) {
                $date = optional($registration->wfhDate)->tanggal;

                return optional($date)->timestamp ?: PHP_INT_MAX;
            })
            ->values();

        $openCount = WfhDate::where('is_active', true)
            ->whereDate('tanggal', '>=', $today)
            ->whereDoesntHave('registrations', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        return [
            'next_registration' => $registrations->first(),
            'upcoming_count' => $registrations->count(),
            'open_count' => $openCount,
        ];
    }
}
