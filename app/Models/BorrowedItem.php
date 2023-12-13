<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowedItem extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        // FK
        'borrowing_transac_id',
        'approver_id',
        'borrowed_item_status_id',
        'item_id',

        'start_date',
        'due_date',
        'date_returned',
        'penalty',
        'remarks'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
