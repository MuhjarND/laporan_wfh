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
            foreach ($kegiatan->evidens as $eviden) {
                $eviden->delete();
            }

            if ($kegiatan->eviden_path) {
                Storage::disk('local')->delete($kegiatan->eviden_path);
            }
        });
    }

    public function laporan()
    {
        return $this->belongsTo(LaporanWfh::class, 'laporan_id');
    }

    public function evidens()
    {
        return $this->hasMany(KegiatanWfhEviden::class, 'kegiatan_id')->orderBy('id');
    }

    public function getAllEvidensAttribute()
    {
        $evidens = $this->relationLoaded('evidens')
            ? $this->evidens
            : $this->evidens()->get();

        if ($evidens->isNotEmpty()) {
            return $evidens;
        }

        if (!$this->eviden_path || !$this->eviden_token) {
            return collect();
        }

        return collect([(object) [
            'preview_link' => route('eviden.preview', $this->eviden_token),
            'embed_url' => route('eviden.file', $this->eviden_token),
            'original_name' => $this->eviden_original_name,
            'token' => $this->eviden_token,
        ]]);
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
