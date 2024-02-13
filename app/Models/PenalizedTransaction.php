<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenalizedTransaction extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'borrowing_transac_id',
        'status_id',
    ];
}
