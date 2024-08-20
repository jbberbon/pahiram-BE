<?php

namespace App\Utils\Constants\SampleData;

class GROUP_CATEGORY_SAMPLE
{
    const GROUP_CATEGORY_ARRAY = [
        "CAMERA" => "CAMERA",
        "LAPTOP" => "LAPTOP",
        "BALL" => "BALL",
        "TABLE" => "TABLE",
        "MICROSCOPE" => "MICROSCOPE",
        "3D_PRINTER" => "3D_PRINTER",
        "MICROCONTROLLER" => "MICROCONTROLLER",
        "SPEAKER" => "SPEAKER",
        "VOLTAGE_TESTER" => "VOLTAGE_TESTER",
        "LAN_CABLING_TOOL" => "LAN_CABLING_TOOL",
        "PAPER" => "PAPER",
        "PENS" => "PENS",
        "PENCIL" => "PENCIL"
    ];

    public function getGroupCategorySampleData(string $category)
    {
        return self::GROUP_CATEGORY_ARRAY[$category] ?? null;
    }
}