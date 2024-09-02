<?php

namespace Tests\Unit\Utils\DateUtil;
use App\Utils\DateUtil;
use Tests\TestCase;

class IsDateLapsedTest extends TestCase
{
    public function test_date_provided_is_not_lapsed()
    {
        $result = DateUtil::isDateLapsed(now()->addHour());

        $this->assertFalse($result);
    }

    public function test_date_provided_is_lapsed()
    {
        $result = DateUtil::isDateLapsed(now()->subHour());

        $this->assertTrue($result);
    }
}