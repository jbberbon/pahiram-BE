<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowedItemStatus extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'borrowed_item_status_code',
        'borrowed_item_status',
        'description'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public static function getIdByStatus($status)
    {
        $status = self::where('borrowed_item_status', $status)->first();

        return $status ? $status->id : null;
    }
    public static function getStatusIdBasedOnCode($code)
    {
        $status = self::where('borrowed_item_status_code', $code)->first();
        return $status ? $status->id : null;
    }
}
