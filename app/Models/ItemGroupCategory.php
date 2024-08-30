<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemGroupCategory extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'category_name',
        'is_consumable'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public static function getCategoryNameById($categoryId)
    {
        $category = self::where('id', $categoryId)->first();
        return $category ? $category->category_name : null;
    }
}
