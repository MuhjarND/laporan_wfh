<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(15);
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        // Redirect based on notification type
        $data = $notification->data;
        if (isset($data['laporan_id'])) {
            $user = auth()->user();
            if ($user->isPegawai()) {
                return redirect()->route('pegawai.laporan.show', $data['laporan_id']);
            } elseif ($user->isAtasan()) {
                return redirect()->route('atasan.monitoring.show-laporan', $data['laporan_id']);
            }
        }

        return redirect()->back();
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back()->with('success', 'Semua notifikasi telah dibaca.');
    }
}
