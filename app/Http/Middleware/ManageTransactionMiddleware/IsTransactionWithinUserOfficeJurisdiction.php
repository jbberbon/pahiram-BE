<?php

namespace App\Http\Middleware\ManageTransactionMiddleware;

use App\Models\BorrowTransaction;
use App\Models\UserDepartment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsTransactionWithinUserOfficeJurisdiction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $userOffice = UserDepartment::where('user_id', $user->id)->first();
        $transaction = BorrowTransaction::find($request['transactionId']);

        if ($userOffice->department_id !== $transaction->department_id) {
            abort(response([
                'status' => false,
                'message' => 'Transaction is not within your department',
                'method' => $request->method(),
            ], 401));
        }

        return $next($request);
    }
}