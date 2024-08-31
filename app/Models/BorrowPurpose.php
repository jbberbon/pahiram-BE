<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowPurpose extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'purpose',
        'description',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at'
    ];

    public static function getIdByPurpose($purpose)
    {
        $purpose = self::where('purpose', $purpose)->first();

        return $purpose ? $purpose->id : null;
    }
    public static function getPurposeById($purposeId)
    {
        $purpose = self::where('id', $purposeId)->first();

        return $purpose ? $purpose->purpose : null;
    }
}
