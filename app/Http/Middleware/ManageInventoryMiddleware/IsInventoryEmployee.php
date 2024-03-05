<?php

namespace App\Http\Middleware\ManageInventoryMiddleware;

use App\Models\Department;
use App\Models\UserDepartment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsInventoryEmployee
{
    private $inventoryOffice;
    public function __construct()
    {
        $this->inventoryOffice = Department::where('department_acronym', 'PLO')->first();
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->inventoryOffice) {
            abort(response([
                'status' => false,
                'message' => 'Unauthorized access',
                'method' => $request->method(),
            ], 401));
        }

        $user = Auth::user();
        $userDepartments = UserDepartment::where('department_id', $this->inventoryOffice->id)
            ->get();
        $userDepartment = $userDepartments->where('user_id', $user->id)->first();

        if (!$userDepartment) {
            abort(response([
                'status' => false,
                'message' => 'Unauthorized access',
                'method' => $request->method(),
            ], 401));
        }

        return $next($request);
    }
}