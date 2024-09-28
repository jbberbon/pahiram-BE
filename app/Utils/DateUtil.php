<?php

namespace App\Utils;
use Carbon\Carbon;


class DateUtil
{
    const STANDARD_DATE_FORMAT = "Y-m-d H:i:s";

    public static function convertStringToCarbonDate($stringDate)
    {
        return Carbon::parse($stringDate)->setTimezone('Asia/Manila');
    }

    public static function hasOverlapBetweenDateRanges(
        string $startDate1,
        string $endDate1,
        string $startDate2,
        string $endDate2
    ): bool {
        $startDate1 = self::convertStringToCarbonDate(stringDate: $startDate1);
        $endDate1 = self::convertStringToCarbonDate(stringDate: $endDate1);

        $startDate2 = self::convertStringToCarbonDate(stringDate: $startDate2);
        $endDate2 = self::convertStringToCarbonDate(stringDate: $endDate2);

        return $startDate1 < $endDate2 && $startDate2 < $endDate1;
    }

    public static function mergeOverlappingDate(array $arg, int $actualActiveItemCount)
    {
        // Phase 1: Gathering all distinct start and end dates
        $tempArray = [];
        $markers = [];

        foreach ($arg as $item) {
            $markers[] = $item['start'];
            $markers[] = $item['end'];
        }

        // Remove duplicates and sort markers
        $markers = array_unique($markers);
        sort($markers);

        // Phase 2: Splitting - Iterate over each item and split based on markers
        foreach ($arg as $i => $item) {
            $remainingBlock = $item;

            foreach ($markers as $j => $marker) {
                $markerUnix = strtotime($marker);
                $startUnix = strtotime($remainingBlock['start']);
                $endUnix = strtotime($remainingBlock['end']);

                // Check if the marker is between the start and end time of the current item
                if ($markerUnix > $startUnix && $markerUnix < $endUnix) {
                    // If previous marker exists
                    if (isset($markers[$j - 1])) {
                        $tempArray[] = [
                            'start' => $markers[$j - 1],
                            'end' => $marker,
                            'count' => $item['count']
                        ];

                        // Adjust the remaining block's start to the current marker
                        $remainingBlock['start'] = $marker;
                    }
                }
            }

            // If there's any remaining block that hasn't been split, add it as is
            if (strtotime($remainingBlock['start']) < $endUnix) {
                $tempArray[] = $remainingBlock;
            }
        }

        // Phase 3: Merging similar start and end time blocks
        $finalArray = array_reduce($tempArray, function ($carry, $item) {
            // Check if there's already an entry for the same start and end time
            $found = false;

            foreach ($carry as &$existingItem) {
                if ($existingItem['start'] === $item['start'] && $existingItem['end'] === $item['end']) {
                    // If found, sum the counts
                    $existingItem['count'] += $item['count'];
                    $found = true;
                    break; // Exit the loop since we found a match
                }
            }

            // If not found, add a new entry
            if (!$found) {
                $carry[] = $item;
            }

            return $carry;
        }, []);

        return $finalArray;
    }

    public static function isDateLapsed(string $date): bool
    {
        $date = Carbon::createFromFormat(self::STANDARD_DATE_FORMAT, $date);

        // If provided date-time is greater than now, then it is not lapsed
        return Carbon::now()->greaterThan($date);
    }
}