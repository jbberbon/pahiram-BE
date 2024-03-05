<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenalizedTransactionStatuses extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'status',
        'description',
    ];

    public static function getIdByStatus($statusName)
    {
        $status = self::where('status', $statusName)->first();

        return $status ? $status->id : null;
    }

    public static function getStatusById($statusId)
    {
        $status = self::where('id', $statusId)->first();

        return $status ? $status->status : null;
    }
}
