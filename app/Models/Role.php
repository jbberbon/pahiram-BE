<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'role',
        'role_code',
        'description'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public static function getIdByRole($role)
    {
        $retrievedRole = self::where('role', $role)->first();

        return $retrievedRole ? $retrievedRole->id : null;
    }
    public static function getRoleById($id)
    {
        $retrievedRole = self::where('id', $id)->first();

        return $retrievedRole ? $retrievedRole->role : null;
    }
}
