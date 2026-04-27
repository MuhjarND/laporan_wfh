<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\LaporanWfh;

class LaporanSubmitted extends Notification
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
            'title' => 'Laporan WFH Baru Diajukan',
            'message' => $this->laporan->user->name . ' mengajukan laporan WFH periode ' . $this->laporan->periode,
            'laporan_id' => $this->laporan->id,
            'type' => 'laporan_submitted',
        ];
    }
}
