<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowPurpose extends Model
{
    use HasFactory;

    protected $fillable = [
        'purpose_code',
        'general_purpose',
        'description'
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at'
    ];
}
