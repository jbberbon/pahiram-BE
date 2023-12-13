<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'course',
        'course_acronym',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
