<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'token',
        'user_id',
        'expiry'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
