<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'app_user_id', 'name', 'nip', 'email', 'phone', 'password', 'role',
        'pangkat', 'jabatan', 'satuan_kerja', 'atasan_id', 'avatar', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    public function bawahan()
    {
        return $this->hasMany(User::class, 'atasan_id');
    }

    public function laporanWfh()
    {
        return $this->hasMany(LaporanWfh::class);
    }

    // Role helpers
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isAtasan()
    {
        return $this->role === 'atasan';
    }

    public function isPegawai()
    {
        return $this->role === 'pegawai';
    }

    public function getFullIdentityAttribute()
    {
        return $this->name . ' (' . $this->nip . ')';
    }
}
