<?php

use Carbon\Carbon;

/**
 * Class DateGuesserTest
 */
class DateGuesserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $expected
     * @param Carbon|null $returned
     */
    protected function responseCheck($expected, $returned)
    {
        if (is_null($expected)) {
            $this->assertNull($returned);
        } else {
            $this->assertEquals($expected->year, $returned->year);
            $this->assertEquals($expected->month, $returned->month);
            $this->assertEquals($expected->day, $returned->day);
            $this->assertEquals($expected->hour, $returned->hour);
            $this->assertEquals($expected->minute, $returned->minute);
            $this->assertEquals($expected->second, $returned->second);
        }
    }

    /**
     *
     */
    public function testBasic()
    {
        $dates = [
            '20/Mar/2017' => Carbon::create(2017, 3, 20),
            '20/03/2017' => Carbon::create(2017, 3, 20),
            '20/3/2017' => Carbon::create(2017, 3, 20),
            '2/3/2017' => Carbon::create(2017, 3, 2),
            '1/3/2017' => Carbon::create(2017, 3, 1),
            '3/1/2017' => Carbon::create(2017, 1, 3),
        ];

        /**
         * @var $dates Carbon[]
         * @var $obj Carbon|null
         */
        foreach ($dates as $date => $expected) {
            $returned = \BespokeSupport\DateGuesser\DateGuesser::create($date);

            if (is_null($expected)) {
                $this->assertNull($returned);
            } else {
                $this->assertNotNull($returned);
            }

            $this->responseCheck($expected, $returned);
        }
    }

    /**
     *
     */
    public function testBasicTime()
    {
        $dates = [
            '20/Mar/2017 13:24' => Carbon::create(2017, 3, 20, 13, 24),
        ];

        /**
         * @var $dates Carbon[]
         * @var $obj Carbon|null
         */
        foreach ($dates as $date => $expected) {
            $returned = \BespokeSupport\DateGuesser\DateGuesser::create($date);

            if (is_null($expected)) {
                $this->assertNull($returned);
            } else {
                $this->assertNotNull($returned);
            }

            $this->responseCheck($expected, $returned);
        }
    }

    /**
     * @expectedException \LogicException
     */
    public function testConstruct()
    {
        new \BespokeSupport\DateGuesser\DateGuesser('30-01-2017');
    }

    /**
     *
     */
    public function testFailures()
    {
        $dates = [
            '11/33/17 23:59:59.000' => null,
            '1/DDD/2017' => null,
            '' => null,
            null => null,
            1 => null,
            false => null,
            'a' => null,
            '111' => null,
        ];

        /**
         * @var $dates Carbon[]
         * @var $obj Carbon|null
         */
        foreach ($dates as $date => $expected) {
            $returned = \BespokeSupport\DateGuesser\DateGuesser::create($date);

            if (is_null($expected)) {
                $this->assertNull($returned);
            } else {
                $this->assertNotNull($returned);
            }

            $this->responseCheck($expected, $returned);
        }
    }

    /**
     *
     */
    public function testInternational()
    {
        $dates = [
            '1/30/2017' => Carbon::create(2017, 01, 30, 0, 0, 0),
        ];

        /**
         * @var $dates Carbon[]
         * @var $obj Carbon|null
         */
        foreach ($dates as $date => $expected) {
            $returned = \BespokeSupport\DateGuesser\DateGuesser::create($date);

            if (is_null($expected)) {
                $this->assertNull($returned);
            } else {
                $this->assertNotNull($returned);
            }

            $this->responseCheck($expected, $returned);
        }
    }

    /**
     *
     */
    public function testObj()
    {
        $obj = Carbon::create(2017, 3, 20);
        $returned = \BespokeSupport\DateGuesser\DateGuesser::create($obj);
        $this->assertEquals(2017, $returned->year);
        $this->assertEquals(3, $returned->month);
        $this->assertEquals(20, $returned->day);
    }

    /**
     *
     */
    public function testNonStandard()
    {
        $returned = \BespokeSupport\DateGuesser\DateGuesser::create('31-12-17');
        $this->assertEquals(2017, $returned->year);
        $this->assertEquals(12, $returned->month);
        $this->assertEquals(31, $returned->day);
    }

    /**
     *
     */
    public function testTextual()
    {
        $returned = \BespokeSupport\DateGuesser\DateGuesser::create('last day of January 2008');
        $this->assertEquals(2008, $returned->year);
        $this->assertEquals(01, $returned->month);
        $this->assertEquals(31, $returned->day);
    }

    /**
     *
     */
    public function testFormatInvalidNew()
    {
        $returned = \BespokeSupport\DateGuesser\DateGuesser::create('31-12-17 13:59');
        $this->assertNull($returned);
    }

    /**
     *
     */
    public function testNewFormat()
    {
        \BespokeSupport\DateGuesser\DateGuesser::$attemptFormatsAdditional[] = 'd-m-y H:i';

        $expected = Carbon::create(2017, 12, 31, 13, 59, 00);
        $returned = \BespokeSupport\DateGuesser\DateGuesser::create('31-12-17 13:59');

        if (is_null($expected)) {
            $this->assertNull($returned);
        } else {
            $this->assertNotNull($returned);
        }

        $this->responseCheck($expected, $returned);
    }

    /**
     *
     */
    public function testNewFormatMicro()
    {
        \BespokeSupport\DateGuesser\DateGuesser::$attemptFormatsAdditional[] = 'd-m-y H:i:s.u';

        $expected = Carbon::create(2017, 12, 31, 13, 59, 59);
        $returned = \BespokeSupport\DateGuesser\DateGuesser::create('31-12-17 13:59:59.01');

        if (is_null($expected)) {
            $this->assertNull($returned);
        } else {
            $this->assertNotNull($returned);
        }

        $this->responseCheck($expected, $returned);

        $this->assertEquals(10000, $returned->micro);
    }
}
