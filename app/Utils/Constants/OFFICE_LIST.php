<?php

namespace App\Utils\Constants;

class OFFICE_LIST
{
    const OFFICE_ARRAY = [
        BMO => [
            acronym => "BMO",
            whole_name => "Buidling Maintenance Office",
            is_lending_office => true
        ],
        ESLO => [
            acronym => "ESLO",
            whole_name => "Engineering and Science Laboratory Office",
            is_lending_office => true
        ],
        ITRO => [
            acronym => "ITRO",
            whole_name => "Information Technology and Resource Office",
            is_lending_office => true
        ],

        // Non Lending Offices
        PLO => [
            acronym => "PLO",
            whole_name => "Purchasing and Logistics Office",
            is_lending_office => false
        ],
        FAO => [
            acronym => "FAO",
            whole_name => "Finance and Accounting Office",
            is_lending_office => false
        ]
    ];

    public static function getOfficeAcronymFromOfficeConstant(string $officeAcronym)
    {
        return self::OFFICE_ARRAY[$officeAcronym]['acronym'] ?? null;
    }
}