<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'note_id',
        'nama_file',
        'path_file',
        'jenis_file', // 'foto' atau 'dokumen'
        'mime_type',
        'ukuran_file',
    ];

    public function note()
    {
        return $this->belongsTo(Note::class);
    }
}
