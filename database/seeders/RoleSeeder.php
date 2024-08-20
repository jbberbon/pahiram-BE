<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Utils\Constants\USER_ROLE;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = USER_ROLE::USER_ROLE_ARRAY;
        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
