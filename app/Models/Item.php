<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'apc_item_id',
        // FK
        'item_group_id',
        'item_status_id',
        'purchase_order_id',
        'located_at',
        'possessed_by',
        'borrowed_by',

        'manufacturer_serial_num',
        'warranty_expiration',
        'unit_cost',
        'supplier_name',
        'supplier_tel_num',
        'supplier_email'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function itemGroup()
    {
        return $this->belongsTo(ItemGroup::class);
    }

}
