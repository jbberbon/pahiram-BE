<?php

namespace App\Utils\Constants;

class OFFICE_LIST
{
    const BMO = 'BMO';
    const ESLO = 'ESLO';
    const ITRO = 'ITRO';

    const FAO = 'FAO';
    const PLO = 'PLO';

    
    const OFFICE_ARRAY = [
        self::BMO => [
            "department_acronym" => self::BMO,
            "department" => "Buidling Maintenance Office",
            "is_lending_office" => true,
        ],
        self::ESLO => [
            "department_acronym" => self::ESLO,
            "department" => "Engineering and Science Laboratory Office",
            "is_lending_office" => true,
        ],
        self::ITRO => [
            "department_acronym" => self::ITRO,
            "department" => "Information Technology and Resource Office",
            "is_lending_office" => true,
        ],

        // Non Lending Offices
        self::PLO => [
            "department_acronym" => self::PLO ,
            "department" => "Purchasing and Logistics Office",
            "is_lending_office" => false
        ],
        self::FAO => [
            "department_acronym" => self::FAO,
            "department" => "Finance and Accounting Office",
            "is_lending_office" => false
        ]
    ];

    public static function getOfficeAcronymFromOfficeConstant(string $officeAcronym)
    {
        return self::OFFICE_ARRAY[$officeAcronym]['department_acronym'] ?? null;
    }
}