<?php

use App\Http\Controllers\BorrowTransaction\ItemGroupController;
use App\Http\Controllers\BorrowTransaction\ManageBorrowingRequestController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BorrowTransaction\ManageBorrowTransactionController;
use App\Http\Controllers\BorrowTransaction\ManageEndorsementController;
use App\Http\Controllers\Inventory\PLOManageInventory;
use App\Http\Controllers\Penalty\ManagePenaltyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public Routes
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/user/logout', [AuthController::class, 'logout']);
    Route::post('/user/logout-all-device', [AuthController::class, 'logoutAllDevices']);

    // Is Suspended
    Route::group(['middleware' => ['is_suspended']], function () {
        Route::get('/office/{departmentAcronym}/item-model-list', [ItemGroupController::class, 'index']);
        Route::post('/user/borrow-request/submit', [ManageBorrowingRequestController::class, 'submitBorrowRequest']);
        Route::patch('/user/borrow-request/{requestId}/edit', [ManageBorrowingRequestController::class, 'editBorrowRequest']);
    });

    Route::get('/user/borrow-request', [ManageBorrowingRequestController::class, 'index']);
    Route::patch('/user/borrow-request/{borrowRequest}/cancel', [ManageBorrowingRequestController::class, 'cancelBorrowRequest']);
    Route::get('/user/borrow-request/{borrowRequest}', [ManageBorrowingRequestController::class, 'getBorrowRequest']);
    Route::get('/user/penalized-transaction', [ManagePenaltyController::class, 'index']);
    Route::group(['middleware' => ['is_penalized_transaction_existent']], function () {
        Route::get('/user/penalized-transaction/{penalizedTransactionId}', [ManagePenaltyController::class, 'show']);
    });


    // Is Endorser
    Route::group(['middleware' => ['is_endorser']], function () {
        Route::get('/user/endorsement', [ManageEndorsementController::class, 'index']);
        Route::get('/borrow-transaction/endorsed/{transactionId}', [ManageEndorsementController::class, 'show']);
        Route::patch('/endorsement/{transactionId}/approval', [ManageEndorsementController::class, 'endorsementApproval']);
    });

    // Is Lending Employee
    Route::group(['middleware' => ['is_lending_employee']], function () {
        // Get HTTP Requests with filtering
        Route::get('/office/borrow-transaction', [ManageBorrowTransactionController::class, 'index']);

        Route::group([
            'middleware' => [
                'is_transaction_existent',
                'is_transaction_within_office_jurisdiction'
            ]
        ], function () {
            // Approve Transaction
            Route::get('/office/borrow-transaction/{transactionId}', [ManageBorrowTransactionController::class, 'getSpecificPendingTransaction']);
            Route::patch('/office/borrow-transaction/{transactionId}/borrow-approval', [ManageBorrowTransactionController::class, 'approveTransaction']);

            // Release Items
            Route::patch('/office/borrow-transaction/{transactionId}/release-item', [ManageBorrowTransactionController::class, 'releaseApprovedItems']);

            // Facilitate Return
            Route::patch('/office/borrow-transaction/{transactionId}/facilitate-item-return', [ManageBorrowTransactionController::class, 'facilitateReturn']);
        });
    });

    // Is PLO Employee
    Route::group(['middleware' => ['is_inventory_employee']], function () {
        Route::get('/inventory', [PLOManageInventory::class, 'index']);
        Route::get('/inventory/{itemId}', [PLOManageInventory::class, 'show']);
    });

    // Is Finance Employee
    Route::group(['middleware' => ['is_finance_employee']], function () {
        Route::get('/penalized-transaction', [ManagePenaltyController::class, 'index']);

        Route::group(['middleware' => ['is_penalized_transaction_existent']], function () {
            Route::get('/penalized-transaction/{penalizedTransactionId}', [ManagePenaltyController::class, 'show']);
            Route::patch('/penalized-transaction/{penalizedTransactionId}/mark-as-paid', [ManagePenaltyController::class, 'payPenalty']);
        });



    });

    Route::get('/item-model/{itemGroupId}/booked-dates', [ItemGroupController::class, 'retrieveBookedDates']);
});
