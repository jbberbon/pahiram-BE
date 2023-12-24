<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Uuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'apc_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'is_admin',
        'user_role_id',
        'course_id',
        'acc_status_id',
        'department_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // 'id',
        'password',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public function getUserRoleId()
    {
        return $this->belongsTo(Role::class, 'user_role_id');
    }
    public function getUserRole()
    {
        return $this->belongsTo(Role::class, 'user_role');
    }
    public function getAccountStatusCode()
    {
        $statusId = $this->acc_status_id;
        $accountStatus = AccountStatus::where('id', $statusId)->first();

        return $accountStatus ? $accountStatus->acc_status_code : null;
    }
    public function getAccountStatus()
    {
        $statusId = $this->acc_status_id;
        $accountStatus = AccountStatus::where('id', $statusId)->first();

        return $accountStatus ? $accountStatus->acc_status : null;
    }

    public static function getUserIdBasedOnApcId($apcId) {
        $user = self::where('apc_id', $apcId)->first();

        return $user ? $user->id : null;
    }
}

