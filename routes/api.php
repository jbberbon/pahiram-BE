<?php

use App\Http\Controllers\BorrowTransaction\BorrowedItemController;
use App\Http\Controllers\BorrowTransaction\ItemGroupController;
use App\Http\Controllers\BorrowTransaction\ManageBorrowingRequestController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BorrowTransaction\ManageBorrowTransactionController;
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


    Route::group(['middleware' => ['is_suspended']], function () {
        Route::post('/user/borrow-request/submit', [ManageBorrowingRequestController::class, 'submitBorrowRequest']);
        Route::patch('/user/borrow-request/{requestId}/edit', [ManageBorrowingRequestController::class, 'editBorrowRequest']);
        Route::get('/office/{departmentAcronym}/item-model-list', [ItemGroupController::class, 'index']);
    });
    Route::get('/user/borrow-request', [ManageBorrowingRequestController::class, 'index']);
    Route::patch('/user/borrow-request/{borrowRequest}/cancel', [ManageBorrowingRequestController::class, 'cancelBorrowRequest']);
    Route::get('/user/borrow-request/{borrowRequest}', [ManageBorrowingRequestController::class, 'getBorrowRequest']);


    // // Route::group(['middleware' => ['is_employee']], function () {
    //     // Route::group(['middleware' => ['is_lending_employee']], function () {
    //         Route::get('/office/borrow-transaction', [ManageBorrowTransactionController::class, 'index']);

    //     // });
    // // });
    // Route::get('/item-model/{itemGroupId}/booked-dates', [ItemGroupController::class, 'retrieveBookedDates']);
});
