<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\LaporanWfh;
use App\WfhDate;

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
        $recentLaporan = LaporanWfh::with('user')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.admin', compact(
            'totalUsers', 'totalPegawai', 'totalAtasan',
            'totalLaporan', 'laporanSubmitted', 'laporanApproved',
            'totalWfhDates', 'recentLaporan'
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

        $recentLaporan = LaporanWfh::with('user')
            ->whereIn('user_id', $bawahanIds)
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

        $recentLaporanSaya = LaporanWfh::where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.atasan', compact(
            'totalBawahan', 'laporanSubmitted', 'laporanApproved',
            'totalLaporan', 'recentLaporan', 'bawahan',
            'laporanSayaTotal', 'laporanSayaDraft', 'laporanSayaSubmitted',
            'laporanSayaApproved', 'currentLaporan', 'recentLaporanSaya'
        ));
    }

    private function pegawaiDashboard()
    {
        $user = auth()->user();
        $totalLaporan = LaporanWfh::where('user_id', $user->id)->count();
        $laporanDraft = LaporanWfh::where('user_id', $user->id)->where('status', 'draft')->count();
        $laporanSubmitted = LaporanWfh::where('user_id', $user->id)->where('status', 'submitted')->count();
        $laporanApproved = LaporanWfh::where('user_id', $user->id)->where('status', 'approved')->count();

        $recentLaporan = LaporanWfh::where('user_id', $user->id)
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

        return view('dashboard.pegawai', compact(
            'totalLaporan', 'laporanDraft', 'laporanSubmitted',
            'laporanApproved', 'recentLaporan', 'currentLaporan'
        ));
    }
}
