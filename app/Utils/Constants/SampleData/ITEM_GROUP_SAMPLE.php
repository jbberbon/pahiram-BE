<?php

namespace App\Utils\Constants\SampleData;

class ITEM_GROUP_SAMPLE
{
    const ITEM_GROUP_SAMPLE_ARRAY = [
        "Canon 200d" => "Canon 200d",
        "MacBook Air M1" => "MacBook Air M1",
        "Arduino Uno R4 WiFi" => "Arduino Uno R4 WiFi",
        "Spalding FIBA 2007" => "Spalding FIBA 2007",
        "Lifetime 6ft Folding Table" => "Lifetime 6ft Folding Table",
        "JBL Quantum Duo Speaker" => "JBL Quantum Duo Speaker",
        "Micron Cresta ZS Microscope" => "Micron Cresta ZS Microscope",
        "FLUKE T150 Voltage Tester" => "FLUKE T150 Voltage Tester",
        "Replicator+ 3D Printer" => "Replicator+ 3D Printer",
    ];

    public function getItemGroupSample(string $itemGroup)
    {
        return self::ITEM_GROUP_SAMPLE_ARRAY[$itemGroup] ?? null;
    }
}