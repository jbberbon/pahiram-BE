<?php

namespace App\Utils\Constants;

class OFFICE_LIST
{
    const OFFICE_ARRAY = [
        "BMO" => [
            "department_acronym" => "BMO",
            "department" => "Buidling Maintenance Office",
            "is_lending_office" => true,
        ],
        "ESLO" => [
            "department_acronym" => "ESLO",
            "department" => "Engineering and Science Laboratory Office",
            "is_lending_office" => true,
        ],
        "ITRO" => [
            "department_acronym" => "ITRO",
            "department" => "Information Technology and Resource Office",
            "is_lending_office" => true,
        ],

        // Non Lending Offices
        "PLO" => [
            "department_acronym" => "PLO",
            "department" => "Purchasing and Logistics Office",
            "is_lending_office" => false
        ],
        "FAO" => [
            "department_acronym" => "FAO",
            "department" => "Finance and Accounting Office",
            "is_lending_office" => false
        ]
    ];

    public static function getOfficeAcronymFromOfficeConstant(string $officeAcronym)
    {
        return self::OFFICE_ARRAY[$officeAcronym]['department_acronym'] ?? null;
    }
}