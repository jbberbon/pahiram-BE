<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApcisToken extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'token',
        'user_id',
        'expires_at'
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at'
    ];
}
