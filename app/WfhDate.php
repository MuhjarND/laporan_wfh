<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WfhDate extends Model
{
    protected $fillable = [
        'tanggal',
        'keterangan',
        'letter_number',
        'letter_status',
        'letter_requested_at',
        'letter_published_at',
        'letter_approved_at',
        'letter_approved_by',
        'letter_signature',
        'letter_notified_at',
        'is_active',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'letter_requested_at' => 'datetime',
        'letter_published_at' => 'datetime',
        'letter_approved_at' => 'datetime',
        'letter_notified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'wfh_date_user')->withTimestamps();
    }

    public function registrations()
    {
        return $this->hasMany(WfhRegistration::class, 'wfh_date_id');
    }

    public function selectedRegistrations()
    {
        return $this->hasMany(WfhRegistration::class, 'wfh_date_id')->where('status', 'selected');
    }

    public function letterApprover()
    {
        return $this->belongsTo(User::class, 'letter_approved_by');
    }
}
