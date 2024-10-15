<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowedItem extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        // FK
        'borrowing_transac_id',
        'approver_id',
        'borrowed_item_status_id',
        'item_id',

        'start_date',
        'due_date',
        'date_returned',
        'penalty',
        'remarks'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function borrowTransaction()
    {
        return $this->belongsTo(BorrowTransaction::class, 'borrowing_transac_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the start date of the borrowed item.
     *
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->start_date;
    }

    /**
     * Get the due date of the borrowed item.
     *
     * @return string
     */
    public function getDueDate(): string
    {
        return $this->due_date;
    }

    public static function getOverdueItemCountByItemGroupId(string $itemGroupId): int
    {
        $inPossessionStatusId = BorrowedItemStatus::getIdByStatus(status: "IN_POSSESSION");

        if (!$inPossessionStatusId) {
            return 0;
        }

        return self::join('items', 'borrowed_items.item_id', '=', 'items.id')
            ->join('item_groups', 'items.item_group_id', '=', 'item_groups.id')
            ->where('item_groups.id', $itemGroupId)
            // Where status is in-possession
            ->where('borrowed_items.borrowed_item_status_id', $inPossessionStatusId)
            //where now() > due date
            ->where('due_date', '<', now())
            ->count();
    }
}
