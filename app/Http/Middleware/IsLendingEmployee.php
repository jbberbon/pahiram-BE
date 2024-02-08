<?php

namespace App\Http\Middleware;

use App\Models\BMO;
use App\Models\Department;
use App\Models\ESLO;
use App\Models\ITRO;
use App\Models\UserDepartment;
use App\Utils\Constants\OFFICE_CODES;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsLendingEmployee
{
    private $bmoId;
    private $esloId;
    private $itroId;

    private $lendingOfficesIds;

    public function __construct()
    {
        $this->bmoId = Department::getIdBasedOnAcronym(OFFICE_CODES::BMO);
        $this->esloId = Department::getIdBasedOnAcronym(OFFICE_CODES::ESLO);
        $this->itroId = Department::getIdBasedOnAcronym(OFFICE_CODES::ITRO);

        $this->lendingOfficesIds = [$this->bmoId, $this->esloId, $this->itroId];
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        $userDepartment = UserDepartment::whereIn('department_id', $this->lendingOfficesIds)
            ->where('user_id', $user->id)
            ->get();

        if ($userDepartment->isEmpty()) {
            abort(response([
                'status' => false,
                'message' => 'Unauthorized access',
                'method' => $request->method(),
            ], 401));
        }

        return $next($request);
    }
}