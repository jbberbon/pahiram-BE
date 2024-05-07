<?php

namespace App\Http\Middleware\ManageAccountRolesMiddleware;

use App\Models\SystemAdmin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        $admin = SystemAdmin::find($user->id);

        if (!$admin) {
            abort(response([
                'status' => false,
                'message' => 'Insufficient permission',
                'method' => $request->method(),
            ], 403));
        }

        return $next($request);
    }
}