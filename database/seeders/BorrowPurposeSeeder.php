<?php

namespace Database\Seeders;

use App\Models\BorrowPurpose;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BorrowPurposeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $purposes = [
            [
                "purpose" => "ACADEMIC_REQUIREMENT",
                "description" => "For general academic projects, assignments, or coursework"
            ],

            [
                "purpose" => "ORG_CLUB_ACTIVITY",
                "description" => "For org or club-related events and activities."
            ],
            [
                "purpose" => "UPSKILLING",
                "description" => "For the purpose of skill development and upskilling"
            ],
            [
                "purpose" => "HOBBY",
                "description" => "For personal hobbies or leisure activities."
            ],
            [
                "purpose" => "SPECIAL_EVENT",
                "description" => "For a special event or occasion."
            ],
            [
                "purpose" => "OTHERS",
                "description" => "User will be prompted to input the purpose"
            ],
        ];
        foreach ($purposes as $purpose) {
            BorrowPurpose::create($purpose);
        }
    }
}
