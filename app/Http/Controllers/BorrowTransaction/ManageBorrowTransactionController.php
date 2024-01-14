<?php

namespace App\Http\Controllers\BorrowTransaction;

use App\Http\Controllers\Controller;
use App\Models\BorrowTransaction;
use App\Models\Department;
use App\Utils\Constants\OfficeCodes;
use Illuminate\Http\Request;

class ManageBorrowTransactionController extends Controller
{
    protected $user;
    protected $lendingOfficesArray;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });

        $this->lendingOfficesArray = OfficeCodes::LENDING_OFFICES_ARRAY;
    }
    /**
     * Display transaction list
     */
    public function index()
    {
        // Check from which office user belongs
        $userDepartmentId = $this->user->department_id;

        $userDepartmentCode = Department::where('id', $userDepartmentId)->firstOrFail()->department_code;

        if (!in_array($userDepartmentCode, $this->lendingOfficesArray)) {
            return response([
                'status' => false,
                'message' => "You are not a lending employee",
                'method' => "GET"
            ]);
        }

        $transactions = BorrowTransaction::where('department_id', $userDepartmentId)->get()->toArray();

        return response([
            'status' => true,
            'data' => $transactions,
            'method' => "GET"
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
