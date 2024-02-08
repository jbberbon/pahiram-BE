<?php

namespace App\Http\Middleware;

use App\Models\AccountStatus;
use App\Utils\Constants\Statuses\ACCOUNT_STATUS;
use Closure;
use Illuminate\Http\Request;

use Symfony\Component\HttpFoundation\Response;

class IsSuspended
{
    private $active = ACCOUNT_STATUS::ACTIVE;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $accStatus = $user->getAccountStatus();

        $method = $request->method();

        if ($accStatus != $this->active) {
            abort(response([
                'status' => false,
                'message' => 'Your account is ' . $accStatus,
                'method' => $method,
            ], 401));
        }
        return $next($request);
    }
}