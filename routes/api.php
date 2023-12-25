<?php

use App\Http\Controllers\BorrowTransaction\ManageBorrowingRequestController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
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

    // CRUD {USERS & COURSES}
    // Route::apiResource('/users', UserController::class);
    // Route::get('/courses', [CourseController::class, 'index']);

    Route::group(['middleware' => ['is_suspended']], function () {
        Route::get('/user/borrow-request', [ManageBorrowingRequestController::class, 'index']);
        Route::post('/user/borrow-request/submit', [ManageBorrowingRequestController::class, 'submitBorrowRequest']);



        // Route::get('/courses/{course}', [CourseController::class, 'show']);
        // Route::post('/courses', [CourseController::class, 'store']);
        // Route::patch('/courses/{course}', [CourseController::class, 'update']);
        // Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
    });
    Route::patch('/user/borrow-request/{borrowRequest}/cancel', [ManageBorrowingRequestController::class, 'cancelBorrowRequest']);
    Route::get('/user/borrow-request/{borrowRequest}', [ManageBorrowingRequestController::class, 'getBorrowRequest']);
});
