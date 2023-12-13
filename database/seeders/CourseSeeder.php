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
        // // set employee token from pahiram-auth
        // $token = "1|NDkSsewBluvj2K7ODSdu5zBNBHioiMKZcp9p334ifd26a5ed"; 
        // $response = Http::withToken($token)->get('http://192.168.162.252/api/courses');

        // if ($response->successful()) {
        //     $data = $response->json();
        //     $courses = $data['data'];

        //     foreach ($courses as $course) {
        //         Course::create($course);
        //     }
        // } else {
        //     // Handle the case where the API request was not successful
        //     $errorMessage = $response->json()['error'] ?? 'API request failed';
        //     $this->command->error("Failed to fetch data from API: $errorMessage");
        // }
    }
}
