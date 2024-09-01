<?php

namespace App\Utils;
use Carbon\Carbon;


class DateUtil
{
    const STANDARD_DATE_FORMAT = "Y-m-d H:i:s";

    public static function hasOverlapBetweenDateRanges(
        string $startDate1,
        string $endDate1,
        string $startDate2,
        string $endDate2
    ): bool {
        $startDate1 = Carbon::createFromFormat(self::STANDARD_DATE_FORMAT, $startDate1);
        $endDate1 = Carbon::createFromFormat(self::STANDARD_DATE_FORMAT, $endDate1);

        $startDate2 = Carbon::createFromFormat(self::STANDARD_DATE_FORMAT, $startDate2);
        $endDate2 = Carbon::createFromFormat(self::STANDARD_DATE_FORMAT, $endDate2);

        return $startDate1 < $endDate2 && $startDate2 < $endDate1;
    }

    public static function isDateLapsed(string $date): bool
    {
        $date = Carbon::createFromFormat(self::STANDARD_DATE_FORMAT, $date);

        // If provided date-time is greater than now, then it is not lapsed
        return Carbon::now()->greaterThan($date);
    }
}