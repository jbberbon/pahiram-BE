<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemStatus extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'item_status_code',
        'item_status',
        'description'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public static function getIdByStatus($status)
    {
        $status = self::where('item_status', $status)->first();

        return $status ? $status->id : null;
    }
}
