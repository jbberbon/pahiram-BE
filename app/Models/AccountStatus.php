<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountStatus extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'acc_status',
        'description',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public static function getStatusById($id)
    {
        $status = self::where('id', $id)->first();
        return $status ? $status->acc_status : null;
    }

    public static function getIdByStatus($status)
    {
        $status = self::where('acc_status', $status)->first();
        return $status ? $status->id : null;
    }


}
