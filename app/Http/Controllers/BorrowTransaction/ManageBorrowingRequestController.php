<?php

namespace App\Http\Controllers\BorrowTransaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowTransaction\CancelBorrowRequest;
use App\Http\Requests\BorrowTransaction\GetBorrowRequest;
use App\Http\Requests\BorrowTransaction\SubmitBorrowRequest;
use App\Http\Resources\BorrowRequestCollection;
use App\Http\Resources\BorrowRequestResource;
use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemStatus;
use App\Models\User;
use App\Services\BorrowingRequestService;
use App\Utils\ItemAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Utils\Constants\ItemStatusConst;
use App\Utils\Constants\BorrowedItemStatusConst;

class ManageBorrowingRequestController extends Controller
{
    protected $borrowingRequestService;
    private $activeItemStatusCode = ItemStatusConst::ACTIVE;
    private $pendingBorrowedItemStatusCode = BorrowedItemStatusConst::PENDING;
    private $maxActiveTransactions = 3;

    public function __construct(BorrowingRequestService $borrowingRequestService)
    {
        $this->borrowingRequestService = $borrowingRequestService;
    }
    /**
     *  Display a listing of the borrow request resource.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $requestList = BorrowTransaction::where('borrower_id', $user->id)->get();

            if ($requestList->isEmpty()) {
                return response([
                    'status' => true,
                    'message' => "No Borrowing Request Sent",
                    'method' => "GET"
                ], 204);
            }

            $requestCollection = new BorrowRequestCollection($requestList);

            return response([
                'status' => true,
                'data' => $requestCollection,
                'method' => "GET"
            ], 200);
        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'An error occurred while fetching borrowing requests.',
                'error' => $e->getMessage(),
                'method' => 'GET',
            ], 500);
        }
    }

    public function getBorrowRequest(GetBorrowRequest $borrowRequest)
    {
        $validatedData = $borrowRequest->validated();
        try {
            $retrievedRequest = BorrowTransaction::where('id', $validatedData['borrowRequest'])->first();

            if ($retrievedRequest) {
                return response([
                    'status' => true,
                    'data' => new BorrowRequestResource($retrievedRequest),
                    'method' => 'GET',
                ], 200);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Borrow request not found.',
                    'method' => 'GET',
                ], 404);
            }
        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'An error occurred while submittitng the borrow request.',
                'error' => $e->getMessage(),
                'method' => 'POST',
            ], 500);
        }
    }

    public function getOngoingRequest()
    {

    }

    /** 
     *  Submit borrowing request
     */
    public function submitBorrowRequest(SubmitBorrowRequest $borrowRequest)
    {
        $validatedData = $borrowRequest->validated();
        $userId = Auth::user()->id;

        // 01. Check if user has > 3 active transactions
        $maxTransactionCheck = $this->borrowingRequestService->checkMaxTransactions($userId);
        if ($maxTransactionCheck) {
            return $maxTransactionCheck;
        }

        // 02. Get all items with "active" status in items TB  
        $requestedItems = $validatedData['items'];
        $activeItems = $this->borrowingRequestService->getActiveItems($requestedItems);

        // 03. Check borrowed_items if which ones are available on that date
        $availableItems = $this->borrowingRequestService->getAvailableItems($activeItems);

        // 04. Requested qty > available items on schedule (Fail)
        $isRequestQtyMoreThanAvailableQty = $this->borrowingRequestService
            ->checkRequestQtyAndAvailableQty($availableItems);
        if ($isRequestQtyMoreThanAvailableQty) {
            return $isRequestQtyMoreThanAvailableQty;
        }

        // 05. Requested qty < available items on schedule (SHUFFLE then Choose)
        $chosenItems = $this->borrowingRequestService->shuffleAvailableItems($availableItems);

        try {
            // 06. Insert new borrowing transaction
            $newBorrowRequest = $this->borrowingRequestService->insertNewBorrowingTransaction($validatedData, $userId);
            // 07. Insert new borrowed items
            $newBorrowedItems = $this->borrowingRequestService->insertNewBorrowedItems($chosenItems, $newBorrowRequest);

            if (!$newBorrowRequest || !$newBorrowedItems) {
                return response([
                    'status' => false,
                    'message' => 'Unable to insert new transaction and its corresponding items.',
                    'method' => 'POST',
                ], 500);
            }

            // FOR Debugging ONLY
            // return response([
            //     'status' => true,
            //     'message' => 'Successfully submitted borrow request',
            //     'borrow_request' => $newBorrowRequest,
            //     'borrowed_items' => $newBorrowedItems,
            //     'method' => 'POST',
            // ], 200);

            return response([
                'status' => true,
                'message' => 'Successfully submitted borrow request',
                'method' => 'POST',
            ], 200);

        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'An error occurred while submittitng the borrow request.',
                'error' => $e->getMessage(),
                'method' => 'POST',
            ], 500);
        }
    }

    /**
     *  Edit borrow request  
     *  !!!!!------> Not a priority
     */
    public function editBorrowRequest(Request $request, BorrowTransaction $borrowTransaction)
    {

    }
    /**
     *  Cancel Borrow Request
     */
    public function cancelBorrowRequest(CancelBorrowRequest $cancelBorrowRequest)
    {
        try {
            $validatedData = $cancelBorrowRequest->validated();

            // Assuming 'cancelled' is the status code you want to set
            $cancelledStatus = BorrowTransactionStatus::where('transac_status_code', 'cancelled')->firstOrFail();

            BorrowTransaction::where('id', $validatedData['borrowRequest'])
                ->update(['transac_status_id' => $cancelledStatus->id]);

            return response([
                'status' => true,
                'message' => 'Successfully cancelled borrow request.',
                'method' => 'PATCH'
            ], 200);
        } catch (\Exception $e) {
            return response([
                'status' => false,
                'message' => 'An error occurred.',
                'method' => 'PATCH',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
