<?php

namespace App\Http\Middleware;

use App\Models\Department;
use App\Models\UserDepartment;
use App\Utils\Constants\OFFICE_LIST;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsLendingEmployee
{
    private $bmoId;
    private $esloId;
    private $itroId;

    private $bmoAcronym;
    private $itroAcronym;
    private $esloAcronym;

    private $lendingOfficesIds;

    public function __construct()
    {
        $this->bmoAcronym = OFFICE_LIST::OFFICE_ARRAY["BMO"]["acronym"];
        $this->itroAcronym = OFFICE_LIST::OFFICE_ARRAY["ITRO"]["acronym"];
        $this->esloAcronym = OFFICE_LIST::OFFICE_ARRAY["ESLO"]["acronym"];

        $this->bmoId = Department::getIdBasedOnAcronym($this->bmoAcronym);
        $this->esloId = Department::getIdBasedOnAcronym($this->esloAcronym);
        $this->itroId = Department::getIdBasedOnAcronym($this->itroAcronym);

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