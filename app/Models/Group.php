<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'creator_id'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function users()
    {
        // Relasi ke User melalui tabel pivot group_user
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }
}