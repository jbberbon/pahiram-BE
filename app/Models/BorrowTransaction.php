<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowTransaction extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'borrower_id',
        'endorsed_by',
        'department_id',
        'transac_status_id',
        'purpose_id',
        'user_defined_purpose',
        'penalty',
        'remarks_by_endorser',
        'remarks_by_approver'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
