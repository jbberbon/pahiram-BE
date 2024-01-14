<?php

namespace App\Services;

use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\BorrowPurpose;
use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemStatus;
use App\Models\User;
use App\Utils\Constants\BorrowedItemStatusConst;
use App\Utils\Constants\ItemStatusConst;
use App\Services\ItemAvailability;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EditBorrowRequestService
{
    private $cancelledBorrowedItemStatusCode;
    private $pendingApprovalCode;
    private $pendingApprovalId;
    private $cancelledBorrowedItemStatusId;

    public function __construct()
    {
        $this->cancelledBorrowedItemStatusCode = BorrowedItemStatusConst::CANCELLED;
        $this->pendingApprovalCode = BorrowedItemStatusConst::PENDING;
        $this->pendingApprovalId = BorrowedItemStatus::where('borrowed_item_status_code', $this->pendingApprovalCode)->first()->id;
        $this->cancelledBorrowedItemStatusId = BorrowedItemStatus::where('borrowed_item_status_code', $this->cancelledBorrowedItemStatusCode)->first()->id;
    }
    public function prepareRequestUpdateArgs($validatedData)
    {
        // Retrieve "transaction" (item data not included) related details and patch it to tb
        $borrowRequestArgs = $validatedData;
        $borrowRequestArgs['endorsed_by'] = User::getUserIdBasedOnApcId($validatedData['endorsed_by']);


        if (isset($borrowRequestArgs['purpose_code'])) {
            unset($borrowRequestArgs['purpose_code']);
        }
        if (isset($borrowRequestArgs['apcis_token'])) {
            unset($borrowRequestArgs['apcis_token']);
        }

        $borrowRequestArgs['purpose_id'] = BorrowPurpose::where('purpose_code', $validatedData['purpose_code'])->first()->id;

        return $borrowRequestArgs;
    }

    public function isCancelled($itemGroup)
    {
        $cancelledItemGroupId = null;
        $itemGroupId = $itemGroup['item_group_id'];

        $hasCancel = isset($itemGroup['is_cancelled']);
        $cancelIsTrue = $hasCancel === true;
        if ($hasCancel && $cancelIsTrue) {
            $cancelledItemGroupId = $itemGroupId;
        }

        return $cancelledItemGroupId === null ? false : $cancelledItemGroupId;
    }

    // public function itemGroupExistsInBorrowedItems($itemGroup, $requestId)
    // {
    //     $itemGroupId = $itemGroup['item_group_id'];

    //     // Retrieve all borrowed items from the specific borrow transaction
    //     $borrowedItems = BorrowedItem::where('borrowing_transac_id', $requestId)->get();

    //     // Check if any of the borrowed items is associated with the provided item_group_id
    //     $existingItem = $borrowedItems->first(function ($item) use ($itemGroupId) {
    //         return $item->item->item_group_id == $itemGroupId;
    //     });

    //     return $existingItem;
    // }
    public function editQtyAndDate($itemGroup)
    {
        $hasQuantity = isset($itemGroup['quantity']);
        $hasDates = isset($itemGroup['start_date']) && isset($itemGroup['return_date']);
        $hasNoCancel = !isset($itemGroup['is_cancelled']);
        if ($hasQuantity && $hasDates && $hasNoCancel) {
            return $itemGroup;
        }
        return false;
    }
    public function editDate($itemGroup)
    {
        $hasDates = isset($itemGroup['start_date']) && isset($itemGroup['return_date']);
        $hasNoQuantity = !isset($itemGroup['quantity']);
        $hasNoCancel = !isset($itemGroup['is_cancelled']);

        if ($hasDates && $hasNoQuantity && $hasNoCancel) {
            return $itemGroup;
        }
        return false;
    }
    public function editQuantity($itemGroup)
    {
        $hasQuantity = isset($itemGroup['quantity']);
        $hasNoDate = !isset($itemGroup['start_date']) && !isset($itemGroup['return_date']);
        $hasNoCancel = !isset($itemGroup['is_cancelled']);

        if ($hasQuantity && $hasNoDate && $hasNoCancel) {
            return $itemGroup;
        }
        return false;
    }

    public function editExistingItem($itemGroup)
    {
        $hasQuantity = isset($itemGroup['quantity']);
        $hasDates = isset($itemGroup['start_date']) && isset($itemGroup['return_date']);
        $hasQtyOrDates = $hasQuantity || $hasDates;
        $hasNoCancel = !isset($itemGroup['is_cancelled']);

        if ($hasQtyOrDates && $hasNoCancel) {
            return $itemGroup;
        }
        return false;
    }


    public function cancelQuery($cancelledItemGroupIds, $requestId)
    {
        if (count($cancelledItemGroupIds) > 0) {
            try {
                // Initialize an array to store retrieved pending item IDs
                $retrievedPendingItemIds = [];
                $retrievedPendingItemData = [];

                // Loop through each cancelled item group ID
                foreach ($cancelledItemGroupIds as $cancelledItemGroupId) {
                    // Query the database to retrieve pending item IDs associated with the cancelled item group
                    $result = DB::table('borrowed_items')
                        ->where('borrowing_transac_id', $requestId)
                        ->where('borrowed_item_status_id', $this->pendingApprovalId)
                        ->join('items', 'borrowed_items.item_id', '=', 'items.id')
                        ->where('items.item_group_id', $cancelledItemGroupId)
                        ->select('borrowed_items.id as borrowed_item_id', 'borrowed_items.start_date', 'borrowed_items.due_date')
                        ->get()
                        ->toArray();

                    // Merge the retrieved item IDs with the overall array
                    $retrievedPendingItemData[$cancelledItemGroupId] = $result;
                    // $retrievedPendingItemIds = array_merge($retrievedPendingItemIds, $result);
                }
                // Begin a database transaction
                DB::beginTransaction();

                // Get only the Borrowed_item_id
                $flattenedBorrowedItemIds = [];
                foreach ($retrievedPendingItemData as $itemGroupData) {
                    // Merge the arrays recursively
                    $flattenedBorrowedItemIds = array_merge_recursive($flattenedBorrowedItemIds, $itemGroupData);
                }
                // Extract the "borrowed_item_id" values
                $resultBorrowedItemIds = array_column($flattenedBorrowedItemIds, 'borrowed_item_id');

                // Update the status of the retrieved pending items to 'Cancelled'
                BorrowedItem::whereIn('id', $resultBorrowedItemIds)->update(['borrowed_item_status_id' => $this->cancelledBorrowedItemStatusId]);

                // Commit the transaction if everything is successful
                DB::commit();

                // Return the array of cancelled item IDs FOR DEBUGGING
                return $retrievedPendingItemData;
            } catch (\Exception $e) {
                // Rollback the transaction in case of an exception and return false
                // DB::rollBack();
                // return $e;
                return false;

            }
        }
    }


    public function editDateQuery()
    {

    }
}