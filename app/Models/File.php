<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    // Tambahkan baris ini agar semua kolom ini boleh diisi
    protected $fillable = [
        'user_id',
        'group_id',
        'name',
        'path',
        'mime_type',
        'size',
    ];

    // Relasi ke User (pastikan sudah ada)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}