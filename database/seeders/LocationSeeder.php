<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                "room" => "MPH1",
                "floor" => "1"
            ],
            [
                "room" => "MPH2",
                "floor" => "3"
            ],
            [
                "room" => "Library",
                "floor" => "7"
            ],
            [
                "room" => "Cafeteria",
                "floor" => "1"
            ],
        ];
        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
