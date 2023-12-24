<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsEmployee
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $userRole = $user->role->role;
        $borrower = 1010;
        if (!$user || !$user->role) {
            abort(response([
                'status' => false,
                'message' => 'Unauthorized access',
                'method' => $request->method(),
            ], 401));
        }

        if ($userRole === $borrower) {
            abort(response([
                'status' => false,
                'message' => 'Unauthorized access',
                'method' => $request->method(),
            ], 401));
        }
        return $next($request);
    }
}