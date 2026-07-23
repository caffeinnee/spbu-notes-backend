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
        'pin_hash',
    ];

    protected $hidden = [
        'pin_hash',
    ];

    protected $casts = [
        'is_pin_locked' => 'boolean',
    ];

    /** Notes belong to a User (Karyawan/Admin) */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Notes has many attachments */
    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
}
