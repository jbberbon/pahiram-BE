<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'apc_purchase_order_id',
        'date_filed',
        'purchase_date',
        'total_cost',
        'requested_by',
        'verified_by',
        'funding_assured_by',
        'approved_by'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
