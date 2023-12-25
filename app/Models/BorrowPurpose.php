<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowPurpose extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'purpose_code',
        'general_purpose',
        'description'
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at'
    ];

    public static function getPurposeById($purposeId)
    {
        $purpose = self::where('id', $purposeId)->first();

        return $purpose->general_purpose;
    }
    public static function getPurposeCodeById($purposeId)
    {
        $purpose = self::where('id', $purposeId)->first();

        return $purpose->purpose_code;
    }
}
