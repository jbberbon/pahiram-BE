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
                'role' => 'Borrower',
                'role_code' => 1010,
                'description' => 'System borrowers'
            ],
            [
                'role' => 'Inventory Manager',
                'role_code' => 2020,
                'description' => 'Manages inventory'
            ],
            [
                'role' => 'Lending Manager',
                'role_code' => 3030,
                'description' => 'Manages lending transactions'
            ],
            [
                'role' => 'Co-supervisor',
                'role_code' => 4040,
                'description' => ''
            ],
            [
                'role' => 'Supervisor',
                'role_code' => 5050,
                'description' => 'Office supervisor'
            ],
        ];
        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
