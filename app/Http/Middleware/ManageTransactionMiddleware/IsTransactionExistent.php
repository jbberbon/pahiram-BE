<?php

namespace App\Http\Middleware\ManageTransactionMiddleware;

use App\Models\BorrowTransaction;
use App\Models\UserDepartment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsTransactionExistent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $transaction = BorrowTransaction::find($request['transactionId']);

        if (!$transaction) {
            abort(response([
                'status' => false,
                'message' => 'Transaction does not exist',
                'method' => $request->method(),
            ], 404));
        }

        return $next($request);
    }
}