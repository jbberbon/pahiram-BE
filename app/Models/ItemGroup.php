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

    public function getGroupItems()
    {
        return $this->belongsTo(Item::class, 'item_group_id');
    }
    public function getActiveItemCount()
    {
        $activeStatus = ItemStatus::where('item_status_code', 2010)->first();
        if (!$activeStatus) {
            // Throw an exception if the active status is not found
            throw new \RuntimeException(response([
                'status' => false,
                'type' => "Exception",
                'message' => "Active status is not found in ItemGroup model getActiveItemCount()",
            ], 404));
        }
        return $this->items()->where('item_status_id', $activeStatus->id)->count();
    }

    public static function getOfficeById($itemGroupId)
    {
        $itemGroup = self::find($itemGroupId);

        if (!$itemGroup) {
            return null;
        }

        return Department::getAcronymById($itemGroup->department_id);

    }
}
