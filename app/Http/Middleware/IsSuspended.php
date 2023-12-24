<?php

namespace App\Http\Middleware;

use App\Models\AccountStatus;
use Closure;
use Illuminate\Http\Request;

use Symfony\Component\HttpFoundation\Response;

class IsSuspended
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $accStatus = $user->getAccountStatus();
        $accStatusCode = $user->getAccountStatusCode();

        $activeCode = "1010";
        $method = $request->method();

        if ($accStatusCode != $activeCode) {
            abort(response([
                'status' => false,
                'message' => 'Your account is ' . $accStatus,
                'method' => $method,
            ], 401));
        }
        return $next($request);
    }
}