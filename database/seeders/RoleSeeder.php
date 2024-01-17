<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'role' => 'BORROWER',
                'description' => 'System borrower'
            ],
            [
                'role' => 'ENDORSER',
                'description' => 'Borrowing Endorser'
            ],
            [
                'role' => 'BORROWING_MANAGER',
                'description' => 'Manages borrowing transactions'
            ],
            [
                'role' => 'INVENTORY_MANAGER',
                'description' => 'Manages inventory'
            ],
            [
                'role' => 'PENALTY_MANAGER',
                'description' => 'Manages borrowing panalties'
            ],
            [
                'role' => 'CO_SUPERVISOR',
                'description' => 'Alter ego of Supervisor but cannot designate office roles'
            ],
            [
                'role' => 'SUPERVISOR',
                'description' => 'Head of the designated office'
            ],
        ];
        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
