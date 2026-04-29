<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WfhDate extends Model
{
    protected $fillable = ['tanggal', 'keterangan', 'is_active'];

    protected $casts = [
        'tanggal' => 'date',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'wfh_date_user')->withTimestamps();
    }
}
