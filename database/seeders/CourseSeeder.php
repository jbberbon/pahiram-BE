<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'course' => 'No Course',
                'course_acronym' => 'N/A'
            ],
        ];
        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
