<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'apc_item_id',
        // FK
        'item_group_id',
        'item_status_id',
        'purchase_order_id',
        'located_at',
        'possessed_by',
        'borrowed_by',

        'manufacturer_serial_num',
        'warranty_expiration',
        'unit_cost',
        'supplier_name',
        'supplier_tel_num',
        'supplier_email'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function itemGroup()
    {
        return $this->belongsTo(ItemGroup::class);
    }

    public static function getApcIdByItemId($itemId)
    {
        $item = self::where('id', $itemId)->first();
        return $item ? $item->apc_item_id : null;
    }
    public static function getModelNameById($itemId)
    {
        $item = self::where('id', $itemId)->first();
        if (!$item) {
            return null;
        }
        $itemGroup = ItemGroup::find($item->item_group_id);
        return $itemGroup ? $itemGroup->model_name : null;
    }

    public static function getActiveItemStatusCountByItemGroupId(string $itemGroupId): int
    {
        $activeItemStatus = ItemStatus::where('item_status', "ACTIVE")->first();
        // If no active item status found, return 0
        if (!$activeItemStatus) {
            return 0;
        }
        return self::where('item_group_id', $itemGroupId)
            ->where('item_status_id', $activeItemStatus->id)
            ->count();
    }

    /**
     * Get the active item count minus the overdue item count by item group ID.
     *
     * @param string $itemGroupId
     * @return object
     */
    public static function getActiveItemStautCountExceptOverdueItems(string $itemGroupId): int
    {
        $activeItemsCount = self::getActiveItemStatusCountByItemGroupId(itemGroupId: $itemGroupId);

        $overdueItemCount = BorrowedItem::getOverdueItemCountByItemGroupId(itemGroupId: $itemGroupId);

        $actualActiveItemsCount = $activeItemsCount - $overdueItemCount;
        return abs(num: $actualActiveItemsCount);
    }
}
