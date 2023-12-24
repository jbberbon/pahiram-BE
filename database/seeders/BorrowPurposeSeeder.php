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
                "purpose_code" => 1010,
                "general_purpose" => "General Academic Requirement/s",
                "description" => "For general academic projects, assignments, or coursework"
            ],
            [
                "purpose_code" => 2020,
                "general_purpose" => "Thesis / Research / Experiment",
                "description" => "For a specific research project or experiment"
            ],
            [
                "purpose_code" => 3030,
                "general_purpose" => "Org / Club Activity",
                "description" => "For org or club-related events and activities."
            ],
            [
                "purpose_code" => 4040,
                "general_purpose" => "Upskilling",
                "description" => "For the purpose of skill development and upskilling"
            ],
            [
                "purpose_code" => 5050,
                "general_purpose" => "Hobby / Leisure",
                "description" => "For personal hobbies or leisure activities."
            ],
            [
                "purpose_code" => 6060,
                "general_purpose" => "Special Event",
                "description" => "For a special event or occasion."
            ],
            [
                "purpose_code" => 7070,
                "general_purpose" => "Others",
                "description" => "User will be prompted to input the purpose"
            ],
        ];
        foreach ($purposes as $purpose) {
            BorrowPurpose::create($purpose);
        }
    }
}
