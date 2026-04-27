<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\LaporanWfh;

class LaporanRejected extends Notification
{
    use Queueable;

    protected $laporan;

    public function __construct(LaporanWfh $laporan)
    {
        $this->laporan = $laporan;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Laporan WFH Ditolak',
            'message' => 'Laporan WFH periode ' . $this->laporan->periode . ' ditolak. Silakan periksa catatan atasan.',
            'laporan_id' => $this->laporan->id,
            'type' => 'laporan_rejected',
        ];
    }
}
