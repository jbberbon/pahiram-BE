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

        $activeCode = "ACTIVE";
        $method = $request->method();

        if ($accStatus != $activeCode) {
            abort(response([
                'status' => false,
                'message' => 'Your account is ' . $accStatus,
                'method' => $method,
            ], 401));
        }
        return $next($request);
    }
}