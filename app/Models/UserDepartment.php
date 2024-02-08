<?php

namespace App\Models;

use App\Traits\UserIdExistsTrait;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDepartment extends Model
{
    use HasFactory, Uuids, UserIdExistsTrait;

    protected $table = 'user_departments';

    protected $fillable = [
        'user_id',
        'department_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
