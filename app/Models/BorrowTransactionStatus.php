<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowTransactionStatus extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'transac_status',
        'transac_status_code',
        'description'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
