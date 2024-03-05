<?php

namespace App\Http\Middleware\ManagePenaltyMiddleware;

use App\Models\Department;
use App\Models\PenalizedTransaction;
use App\Models\UserDepartment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsPenalizedTransactionExistent
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
        $penalizedTransaction = PenalizedTransaction::where('id', $request['penalizedTransactionId'])->first();

        if (!$penalizedTransaction) {
            abort(response([
                'status' => false,
                'message' => 'Invalid penalized transaction ID',
                'method' => $request->method(),
            ], 404));
        }
        return $next($request);
    }
}