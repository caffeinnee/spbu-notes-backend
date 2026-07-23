<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        'user_id',
        'judul',
        'isi',
        'is_pin_locked',
        'pin_code',
        'foto_paths',
        'dokumen_paths',
    ];

    protected $casts = [
        'is_pin_locked' => 'boolean',
    ];

    /** Notes belong to a User (Karyawan) */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
