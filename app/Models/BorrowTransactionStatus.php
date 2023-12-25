<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowTransactionStatus extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'transac_status',
        'transac_status_code',
        'description'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get the id of a specific transaction status by code.
     *
     * @param int $statusCode
     * @return string|null
     */
    public static function getStatusIdByCode($statusCode)
    {
        $status = self::where('transac_status_code', $statusCode)->first();

        return $status ? $status->id : null;
    }

    public static function getStatusById($statusId)
    {
        $status = self::where('id', $statusId)->first();

        return $status ? $status->transac_status : null;
    }
    public static function getStatusCodeById($statusId)
    {
        $status = self::where('id', $statusId)->first();

        return $status ? $status->transac_status_code : null;
    }



}
