<?php

namespace App\Http\Middleware\ManageAccountRolesMiddleware;

use App\Models\Role;
use App\Utils\Constants\USER_ROLE;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsSupervisor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        $supervisorRoleId = Role::getIdByRole(USER_ROLE::SUPERVISOR);

        if ($user->user_role_id != $supervisorRoleId) {
            abort(response([
                'status' => false,
                'message' => 'Insufficient permission',
                'method' => $request->method(),
            ], 403));
        }

        return $next($request);
    }
}