<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class KegiatanWfhEviden extends Model
{
    protected $table = 'kegiatan_wfh_evidens';

    protected $fillable = [
        'kegiatan_id', 'token', 'path', 'original_name', 'mime', 'size',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($eviden) {
            if ($eviden->path) {
                Storage::disk('local')->delete($eviden->path);
            }
        });
    }

    public function kegiatan()
    {
        return $this->belongsTo(KegiatanWfh::class, 'kegiatan_id');
    }

    public function getPreviewLinkAttribute()
    {
        return route('eviden.preview', $this->token);
    }

    public function getEmbedUrlAttribute()
    {
        return route('eviden.file', $this->token);
    }
}
