<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemGroup extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'model_name',
        'is_required_supervisor_approval',
        'total_quantity',
        'available_quantity',
        //FK
        'group_category_id',
        'department_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
