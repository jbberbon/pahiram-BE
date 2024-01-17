<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class SystemAdmin extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'user_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function isAdmin($user)
    {
        return self::where('user_id', $user)->exists();
    }
}
