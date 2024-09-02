<?php

namespace Tests\Unit\Utils\DateUtil;

use Tests\TestCase;
use App\Utils\DateUtil;

class HasOverlapBetweenDateRangesTest extends TestCase
{
    /** @test */
    public function test_no_overlap()
    {
        $startDate1 = '2024-09-01 10:00:00';
        $endDate1 = '2024-09-01 12:00:00';
        $startDate2 = '2024-09-01 13:00:00';
        $endDate2 = '2024-09-01 15:00:00';

        $result = DateUtil::hasOverlapBetweenDateRanges($startDate1, $endDate1, $startDate2, $endDate2);
        $this->assertFalse($result);
    }

    /** @test */
    public function test_overlap_start_inside()
    {
        $startDate1 = '2024-09-01 10:00:00';
        $endDate1 = '2024-09-01 14:00:00';
        $startDate2 = '2024-09-01 12:00:00';
        $endDate2 = '2024-09-01 16:00:00';

        $result = DateUtil::hasOverlapBetweenDateRanges($startDate1, $endDate1, $startDate2, $endDate2);
        $this->assertTrue($result);
    }

    /** @test */
    public function test_overlap_end_inside()
    {
        $startDate1 = '2024-09-01 12:00:00';
        $endDate1 = '2024-09-01 16:00:00';
        $startDate2 = '2024-09-01 10:00:00';
        $endDate2 = '2024-09-01 14:00:00';

        $result = DateUtil::hasOverlapBetweenDateRanges($startDate1, $endDate1, $startDate2, $endDate2);
        $this->assertTrue($result);
    }

    /** @test */
    public function test_full_overlap()
    {
        $startDate1 = '2024-09-01 10:00:00';
        $endDate1 = '2024-09-01 18:00:00';
        $startDate2 = '2024-09-01 12:00:00';
        $endDate2 = '2024-09-01 14:00:00';

        $result = DateUtil::hasOverlapBetweenDateRanges($startDate1, $endDate1, $startDate2, $endDate2);
        $this->assertTrue($result);
    }

    /** @test */
    public function test_exact_match()
    {
        $startDate1 = '2024-09-01 10:00:00';
        $endDate1 = '2024-09-01 18:00:00';
        $startDate2 = '2024-09-01 10:00:00';
        $endDate2 = '2024-09-01 18:00:00';

        $result = DateUtil::hasOverlapBetweenDateRanges($startDate1, $endDate1, $startDate2, $endDate2);
        $this->assertTrue($result);
    }

    /** @test */
    public function test_adjacent_times()
    {
        $startDate1 = '2024-09-01 10:00:00';
        $endDate1 = '2024-09-01 12:00:00';
        $startDate2 = '2024-09-01 12:00:00';
        $endDate2 = '2024-09-01 14:00:00';

        $result = DateUtil::hasOverlapBetweenDateRanges($startDate1, $endDate1, $startDate2, $endDate2);
        $this->assertFalse($result);
    }
}

