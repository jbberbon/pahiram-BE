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

    public static function getIdByStatus($status)
    {
        $status = self::where('status', $status)->first();

        return $status ? $status->id : null;
    }
}
