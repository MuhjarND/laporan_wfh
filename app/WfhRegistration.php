<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WfhRegistration extends Model
{
    protected $fillable = [
        'wfh_date_id', 'user_id', 'status', 'selected_at', 'not_selected_reason',
    ];

    protected $casts = [
        'selected_at' => 'datetime',
    ];

    public function wfhDate()
    {
        return $this->belongsTo(WfhDate::class, 'wfh_date_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
