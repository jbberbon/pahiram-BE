<?php

namespace App\Http\Middleware\ManagePenaltyMiddleware;

use App\Models\Department;
use App\Models\UserDepartment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsFinanceEmployee
{
    private $financeOffice;
    public function __construct()
    {
        $this->financeOffice = Department::where('department_acronym', 'FAD')->first();
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->financeOffice) {
            abort(response([
                'status' => false,
                'message' => 'Unauthorized access',
                'method' => $request->method(),
            ], 401));
        }

        $user = Auth::user();
        $userDepartments = UserDepartment::where('department_id', $this->financeOffice->id)
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