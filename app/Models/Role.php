<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'role',
        'role_code',
        'description'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
