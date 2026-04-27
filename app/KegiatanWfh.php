<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KegiatanWfh extends Model
{
    protected $table = 'kegiatan_wfh';

    protected $fillable = [
        'laporan_id', 'tanggal', 'kegiatan', 'capaian', 'tempat_wfh',
        'eviden_token', 'eviden_path', 'eviden_original_name',
        'eviden_mime', 'eviden_size',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($kegiatan) {
            if (empty($kegiatan->eviden_token)) {
                $kegiatan->eviden_token = (string) Str::uuid();
            }
        });

        static::deleting(function ($kegiatan) {
            if ($kegiatan->eviden_path) {
                Storage::disk('local')->delete($kegiatan->eviden_path);
            }
        });
    }

    public function laporan()
    {
        return $this->belongsTo(LaporanWfh::class, 'laporan_id');
    }

    public function getEvidenPreviewLinkAttribute()
    {
        if (!$this->eviden_path || !$this->eviden_token) {
            return null;
        }

        return route('eviden.preview', $this->eviden_token);
    }

    public function getEvidenEmbedUrlAttribute()
    {
        if (!$this->eviden_path || !$this->eviden_token) {
            return null;
        }

        return route('eviden.file', $this->eviden_token);
    }
}
