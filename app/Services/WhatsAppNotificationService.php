<?php

namespace App\Services;

use App\LaporanWfh;
use App\AppSetting;
use App\User;
use App\WfhDate;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class WhatsAppNotificationService
{
    protected $client;
    protected $endpoint;
    protected $token;
    protected $countryCode;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 15]);
        $this->endpoint = config('services.fonnte.endpoint');
        $this->token = config('services.fonnte.token');
        $this->countryCode = config('services.fonnte.country_code', '62');
    }

    public function sendSubmittedToAtasan(LaporanWfh $laporan)
    {
        $laporan->load('user.atasan');
        $atasan = $laporan->user->atasan;

        if (!$atasan || !$atasan->phone) {
            return false;
        }

        $message = implode("\n", [
            '*[NOTIF LAPWFH]*',
            '',
            "Assalamu'alaikum wr wb.",
            '',
            'Yth. Bapak/Ibu ' . $atasan->name . ',',
            '',
            'Terdapat pengajuan Laporan WFH yang memerlukan tindak lanjut dari Atasan Langsung.',
            '',
            'Nama Pegawai: ' . $laporan->user->name,
            'NIP: ' . $laporan->user->nip,
            'Periode: ' . $laporan->periode,
            'Jumlah Kegiatan: ' . $laporan->kegiatan()->count() . ' kegiatan',
            'Waktu Pengajuan: ' . ($laporan->submitted_at ? $laporan->submitted_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i')),
            '',
            'Silakan melakukan pemeriksaan dan tindak lanjut melalui tautan berikut:',
            route('atasan.monitoring.show-laporan', $laporan) . '?tindaklanjuti=1',
            '',
            'Setujui semua pengajuan pending melalui tautan berikut:',
            route('atasan.monitoring.sign-all'),
            '',
            'Terima kasih.',
            "Wassalamu'alaikum wr wb.",
            '',
            '- *Sistem Laporan WFH PTA Papua Barat*',
        ]);

        return $this->send($atasan->phone, $message);
    }

    public function sendApprovedToPegawai(LaporanWfh $laporan)
    {
        $laporan->load('user', 'approver');

        if (!$laporan->user || !$laporan->user->phone) {
            return false;
        }

        $message = implode("\n", [
            '*[NOTIF LAPWFH]*',
            '',
            "Assalamu'alaikum wr wb.",
            '',
            'Yth. Bapak/Ibu ' . $laporan->user->name . ',',
            '',
            'Laporan WFH Saudara/i telah disetujui oleh Atasan Langsung.',
            '',
            'Periode: ' . $laporan->periode,
            'Disetujui oleh: ' . ($laporan->approver->name ?? 'Atasan Langsung'),
            'Waktu Persetujuan: ' . ($laporan->approved_at ? $laporan->approved_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i')),
            '',
            'Silakan melihat detail laporan melalui tautan berikut:',
            route('pegawai.laporan.show', $laporan),
            '',
            'Terima kasih.',
            "Wassalamu'alaikum wr wb.",
            '',
            '- *Sistem Laporan WFH PTA Papua Barat*',
        ]);

        return $this->send($laporan->user->phone, $message);
    }

    public function sendRejectedToPegawai(LaporanWfh $laporan)
    {
        $laporan->load('user');

        if (!$laporan->user || !$laporan->user->phone) {
            return false;
        }

        $message = implode("\n", [
            '*[NOTIF LAPWFH]*',
            '',
            "Assalamu'alaikum wr wb.",
            '',
            'Yth. Bapak/Ibu ' . $laporan->user->name . ',',
            '',
            'Laporan WFH Saudara/i belum dapat disetujui dan dikembalikan untuk perbaikan.',
            '',
            'Periode: ' . $laporan->periode,
            'Catatan Atasan: ' . ($laporan->catatan_atasan ?: '-'),
            '',
            'Silakan melihat detail laporan melalui tautan berikut:',
            route('pegawai.laporan.show', $laporan),
            '',
            'Terima kasih.',
            "Wassalamu'alaikum wr wb.",
            '',
            '- *Sistem Laporan WFH PTA Papua Barat*',
        ]);

        return $this->send($laporan->user->phone, $message);
    }

    public function sendAccountCredential(User $user, $plainPassword)
    {
        if (!$user->phone) {
            return false;
        }

        $message = implode("\n", [
            '*[NOTIF LAPWFH]*',
            '',
            "Assalamu'alaikum wr wb.",
            '',
            'Yth. Bapak/Ibu ' . $user->name . ',',
            '',
            'Berikut kami sampaikan informasi akun untuk mengakses Sistem Laporan WFH PTA Papua Barat.',
            '',
            'Username (NIP): ' . $user->nip,
            'Password: ' . $plainPassword,
            '',
            'Silakan masuk melalui tautan berikut:',
            route('login'),
            '',
            'Demi keamanan akun, mohon menjaga kerahasiaan password dan tidak membagikannya kepada pihak lain.',
            '',
            'Terima kasih.',
            "Wassalamu'alaikum wr wb.",
            '',
            '- *Sistem Laporan WFH PTA Papua Barat*',
        ]);

        return $this->send($user->phone, $message);
    }

    public function sendWfhReportReminder(User $user, WfhDate $wfhDate)
    {
        if (!$user->phone) {
            return false;
        }

        $message = implode("\n", [
            '*[NOTIF LAPWFH]*',
            '',
            "Assalamu'alaikum wr wb.",
            '',
            'Yth. Bapak/Ibu ' . $user->name . ',',
            '',
            'Berdasarkan monitoring Sistem Laporan WFH, Saudara/i tercatat belum mengisi laporan kegiatan WFH untuk tanggal berikut:',
            '',
            'Tanggal WFH: ' . $wfhDate->tanggal->format('d/m/Y'),
            'Keterangan: ' . ($wfhDate->keterangan ?: '-'),
            '',
            'Mohon segera mengisi laporan kegiatan melalui tautan berikut:',
            route('pegawai.laporan.index'),
            '',
            'Apabila laporan sudah dibuat, mohon abaikan pesan ini.',
            '',
            'Terima kasih.',
            "Wassalamu'alaikum wr wb.",
            '',
            '- *Sistem Laporan WFH PTA Papua Barat*',
        ]);

        return $this->send($user->phone, $message);
    }

    public function sendWfhSubmitReminder(User $user, WfhDate $wfhDate)
    {
        if (!$user->phone) {
            return false;
        }

        $message = implode("\n", [
            '*[NOTIF LAPWFH]*',
            '',
            "Assalamu'alaikum wr wb.",
            '',
            'Yth. Bapak/Ibu ' . $user->name . ',',
            '',
            'Berdasarkan monitoring Sistem Laporan WFH, Saudara/i tercatat sudah mengisi kegiatan WFH namun belum mengirim/mengajukan laporan kepada atasan.',
            '',
            'Tanggal WFH: ' . $wfhDate->tanggal->format('d/m/Y'),
            'Periode: ' . $wfhDate->tanggal->format('m/Y'),
            '',
            'Mohon segera membuka laporan WFH dan menekan tombol Ajukan ke Atasan melalui tautan berikut:',
            route('pegawai.laporan.index'),
            '',
            'Apabila laporan sudah diajukan, mohon abaikan pesan ini.',
            '',
            'Terima kasih.',
            "Wassalamu'alaikum wr wb.",
            '',
            '- *Sistem Laporan WFH PTA Papua Barat*',
        ]);

        return $this->send($user->phone, $message);
    }

    public function sendWfhAssignmentLetterPublished(User $user, WfhDate $wfhDate)
    {
        if (!$user->phone) {
            return false;
        }

        $accessUrl = URL::temporarySignedRoute(
            'wfh-letter-link.open',
            now()->addDays(14),
            ['wfhDate' => $wfhDate->id, 'user' => $user->id, 'type' => 'letter']
        );

        $message = implode("\n", [
            '*[NOTIF LAPWFH]*',
            '',
            "Assalamu'alaikum wr wb.",
            '',
            'Yth. Bapak/Ibu ' . $user->name . ',',
            '',
            'Surat tugas pelaksanaan WFH Saudara/i telah diterbitkan.',
            '',
            'Tanggal WFH: ' . $wfhDate->tanggal->format('d/m/Y'),
            'Nomor Surat: ' . ($wfhDate->letter_number ?: '-'),
            'Keterangan: ' . ($wfhDate->keterangan ?: '-'),
            '',
            'Silakan mengunduh surat tugas melalui tautan berikut:',
            $accessUrl,
            '',
            'Terima kasih.',
            "Wassalamu'alaikum wr wb.",
            '',
            '- *Sistem Laporan WFH PTA Papua Barat*',
        ]);

        return $this->send($user->phone, $message);
    }

    public function sendWfhLetterApprovalRequest(User $approver, WfhDate $wfhDate)
    {
        if (!$approver->phone) {
            return false;
        }

        $wfhDate->loadCount(['registrations', 'selectedRegistrations']);

        $message = implode("\n", [
            '*[NOTIF LAPWFH]*',
            '',
            "Assalamu'alaikum wr wb.",
            '',
            'Yth. Bapak/Ibu ' . $approver->name . ',',
            '',
            'Terdapat surat tugas WFH yang memerlukan pemeriksaan dan tanda tangan.',
            '',
            'Nomor Surat: ' . ($wfhDate->letter_number ?: '-'),
            'Tanggal WFH: ' . $wfhDate->tanggal->format('d/m/Y'),
            'Pegawai Terpilih: ' . $wfhDate->selected_registrations_count . ' pegawai',
            'Total Pendaftar: ' . $wfhDate->registrations_count . ' pegawai',
            '',
            'Silakan melakukan preview dan tanda tangan melalui tautan berikut:',
            route('wfh-letter-approvals.show', $wfhDate),
            '',
            'Terima kasih.',
            "Wassalamu'alaikum wr wb.",
            '',
            '- *Sistem Laporan WFH PTA Papua Barat*',
        ]);

        return $this->send($approver->phone, $message);
    }

    public function sendWfhAssignmentNotSelected(User $user, WfhDate $wfhDate, $reason = null)
    {
        if (!$user->phone) {
            return false;
        }

        $accessUrl = URL::temporarySignedRoute(
            'wfh-letter-link.open',
            now()->addDays(14),
            ['wfhDate' => $wfhDate->id, 'user' => $user->id, 'type' => 'status']
        );

        $message = implode("\n", [
            '*[NOTIF LAPWFH]*',
            '',
            "Assalamu'alaikum wr wb.",
            '',
            'Yth. Bapak/Ibu ' . $user->name . ',',
            '',
            'Berdasarkan hasil seleksi sistem, Saudara/i belum terpilih sebagai peserta WFH pada tanggal berikut:',
            '',
            'Tanggal WFH: ' . $wfhDate->tanggal->format('d/m/Y'),
            'Nomor Surat: ' . ($wfhDate->letter_number ?: '-'),
            'Alasan: ' . ($reason ?: 'Kuota WFH tanggal ini sudah terpenuhi.'),
            '',
            'Silakan melihat status pendaftaran melalui tautan berikut:',
            $accessUrl,
            '',
            'Terima kasih.',
            "Wassalamu'alaikum wr wb.",
            '',
            '- *Sistem Laporan WFH PTA Papua Barat*',
        ]);

        return $this->send($user->phone, $message);
    }

    public function send($phone, $message)
    {
        if (!$this->notificationsEnabled()) {
            Log::info('Pengiriman notifikasi WhatsApp dilewati karena notifikasi WA sedang dinonaktifkan.');

            return false;
        }

        $target = $this->normalizePhone($phone);

        if (!$target || !$this->token || !$this->endpoint) {
            return false;
        }

        try {
            $response = $this->client->post($this->endpoint, [
                'headers' => [
                    'Authorization' => $this->token,
                ],
                'multipart' => [
                    [
                        'name' => 'target',
                        'contents' => $target,
                    ],
                    [
                        'name' => 'message',
                        'contents' => $message,
                    ],
                    [
                        'name' => 'countryCode',
                        'contents' => $this->countryCode,
                    ],
                ],
            ]);

            $body = json_decode((string) $response->getBody(), true);
            if (is_array($body) && (
                (array_key_exists('status', $body) && $body['status'] === false) ||
                (array_key_exists('Status', $body) && $body['Status'] === false)
            )) {
                Log::warning('Fonnte menolak pengiriman notifikasi WhatsApp.', [
                    'target' => $target,
                    'response' => $body,
                ]);

                return false;
            }

            return true;
        } catch (GuzzleException $exception) {
            Log::warning('Gagal mengirim notifikasi WhatsApp Fonnte.', [
                'target' => $target,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    protected function normalizePhone($phone)
    {
        $phone = preg_replace('/\D+/', '', (string) $phone);

        if ($phone === '') {
            return null;
        }

        if (strpos($phone, '0') === 0) {
            return $this->countryCode . substr($phone, 1);
        }

        return $phone;
    }

    public function notificationsEnabled()
    {
        return AppSetting::bool('wa_notifications_enabled', true);
    }
}
