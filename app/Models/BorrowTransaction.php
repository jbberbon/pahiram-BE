<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Models\User;

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
        'remarks_by_approver',
        'created_at',

    ];

    protected $hidden = [
        'updated_at'
    ];

    public function borrower()
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }
}
