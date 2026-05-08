<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LaporanWfh extends Model
{
    protected $table = 'laporan_wfh';

    protected $fillable = [
        'user_id', 'bulan', 'tahun', 'status',
        'catatan_atasan', 'signature_pegawai', 'signature_atasan',
        'submitted_at', 'approved_at', 'approved_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function kegiatan()
    {
        return $this->hasMany(KegiatanWfh::class, 'laporan_id')->orderBy('tanggal', 'asc');
    }

    // Helpers
    public function getNamaBulanAttribute()
    {
        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return $bulanNames[$this->bulan] ?? '';
    }

    public function getPeriodeAttribute()
    {
        return $this->nama_bulan . ' ' . $this->tahun;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => '<span class="badge badge-secondary">Draft</span>',
            'submitted' => '<span class="badge badge-info">Diajukan</span>',
            'approved' => '<span class="badge badge-success">Disetujui</span>',
            'rejected' => '<span class="badge badge-danger">Ditolak</span>',
        ];
        return $badges[$this->status] ?? '';
    }

    public function getPeriodEndsAtAttribute()
    {
        return Carbon::create((int) $this->tahun, (int) $this->bulan, 1)->endOfMonth()->endOfDay();
    }

    public function getPeriodHasEndedAttribute()
    {
        return now()->gt($this->period_ends_at);
    }
}
