<?php

namespace App\Utils\Constants;

class BORROW_PURPOSE
{
    const ACADEMIC_REQUIREMENT = 'ACADEMIC_REQUIREMENT';
    const ORG_ACTIVITY = 'ORG_ACTIVITY';
    const UPSKILLING = 'UPSKILLING';
    const HOBBY = 'HOBBY';
    const SPECIAL_EVENT = 'SPECIAL_EVENT';
    const OTHERS = 'OTHERS';

    const PURPOSE_ARRAY = [
        "ACADEMIC_REQUIREMENT" => [
            "purpose" => self::ACADEMIC_REQUIREMENT,
            "description" => "For general academic projects, assignments, or coursework"
        ],
        "ORG_CLUB_ACTIVITY" => [
            "purpose" => self::ORG_CLUB_ACTIVITY,
            "description" => "For org or club-related events and activities."
        ],
        "UPSKILLING" => [
            "purpose" => self::UPSKILLING,
            "description" => "For the purpose of skill development and upskilling"
        ],
        "HOBBY" => [
            "purpose" => self::HOBBY,
            "description" => "For personal hobbies or leisure activities."
        ],
        "SPECIAL_EVENT" => [
            "purpose" => self::SPECIAL_EVENT,
            "description" => "For a special event or occasion."
        ],
        "OTHERS" => [
            "purpose" => self::OTHERS,
            "description" => "User will be prompted to input the purpose"
        ],

    ];
}