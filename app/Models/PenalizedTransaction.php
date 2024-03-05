<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenalizedTransaction extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'borrowing_transac_id',
        'status_id',
        'receipt_number',
        'payment_facilitated_by',
        'paid_at',
        'remarks_by_cashier',
        'remarks_by_supervisor',
        'settled_at',
        'settlement_facilitated_by'
    ];
}
