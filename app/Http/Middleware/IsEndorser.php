<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsEndorser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $employeeEmailSuffix = "@apc.edu.ph";
        if (!$user || !\Str::endsWith($user->email, $employeeEmailSuffix)) {
            abort(response([
                'status' => false,
                'message' => 'Unauthorized access',
                'method' => $request->method(),
            ], 401));
        }

        return $next($request);
    }
}