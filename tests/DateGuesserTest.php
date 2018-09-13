<?php

use Carbon\Carbon;

/**
 * Class DateGuesserTest.
 */
class DateGuesserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $expected
     * @param Carbon|null $returned
     * @param $input
     */
    protected function responseCheck($expected, $returned, $input)
    {
        if ($expected === null) {
            $this->assertNull($returned);
        } else {
            $this->assertEquals($expected->year, $returned->year, "'$input' '$expected' '$returned'");
            $this->assertEquals($expected->month, $returned->month, "'$input' '$expected' '$returned'");
            $this->assertEquals($expected->day, $returned->day, "'$input' '$expected' '$returned'");
            $this->assertEquals($expected->hour, $returned->hour, "'$input' '$expected' '$returned'");
            $this->assertEquals($expected->minute, $returned->minute, "'$input' '$expected' '$returned'");
            $this->assertEquals($expected->second, $returned->second, "'$input' '$expected' '$returned'");
        }
    }

    public function testBasic()
    {
        $dates = [
            '20/Mar/2017' => Carbon::create(2017, 3, 20, 0, 0, 0),
            '20/03/2017'  => Carbon::create(2017, 3, 20, 0, 0, 0),
            '20/3/2017'   => Carbon::create(2017, 3, 20, 0, 0, 0),
            '2/3/2017'    => Carbon::create(2017, 3, 2, 0, 0, 0),
            '1/3/2017'    => Carbon::create(2017, 3, 1, 0, 0, 0),
            '3/1/2017'    => Carbon::create(2017, 1, 3, 0, 0, 0),
        ];

        /**
         * @var Carbon[]
         * @var $obj     Carbon|null
         */
        foreach ($dates as $date => $expected) {
            $returned = \BespokeSupport\DateGuesser\DateGuesser::create($date);

            if ($expected === null) {
                $this->assertNull($returned);
            } else {
                $this->assertNotNull($returned);
            }

            $this->responseCheck($expected, $returned, $date);
        }
    }

    public function testBasicTime()
    {
        $dates = [
            '20/Mar/2017 13:24' => Carbon::create(2017, 3, 20, 13, 24),
        ];

        /**
         * @var Carbon[]
         * @var $obj     Carbon|null
         */
        foreach ($dates as $date => $expected) {
            $returned = \BespokeSupport\DateGuesser\DateGuesser::create($date);

            if ($expected === null) {
                $this->assertNull($returned);
            } else {
                $this->assertNotNull($returned);
            }

            $this->responseCheck($expected, $returned, $date);
        }
    }

    /**
     * @expectedException \LogicException
     */
    public function testConstruct()
    {
        new \BespokeSupport\DateGuesser\DateGuesser('30-01-2017');
    }

    public function testFailures()
    {
        $dates = [
            '11/33/17 23:59:59.000' => null,
            '1/DDD/2017'            => null,
            ''                      => null,
            '+'                     => null,
            null                    => null,
            1                       => null,
            false                   => null,
            'a'                     => null,
            '111'                   => null,
        ];

        /**
         * @var Carbon[]
         * @var $obj     Carbon|null
         */
        foreach ($dates as $date => $expected) {
            $returned = \BespokeSupport\DateGuesser\DateGuesser::create($date);
            if ($expected === null) {
                $this->assertNull($returned, 'Invalid Date = ' . $date);
            } else {
                $this->assertNotNull($returned);
            }

            $this->responseCheck($expected, $returned, $date);
        }
    }

    public function testInternational()
    {
        $dates = [
            '1/30/2017' => Carbon::create(2017, 01, 30, 0, 0, 0),
        ];

        /**
         * @var Carbon[]
         * @var $obj     Carbon|null
         */
        foreach ($dates as $date => $expected) {
            $returned = \BespokeSupport\DateGuesser\DateGuesser::create($date);

            if ($expected === null) {
                $this->assertNull($returned);
            } else {
                $this->assertNotNull($returned);
            }

            $this->responseCheck($expected, $returned, $date);
        }
    }

    public function testObj()
    {
        $obj = Carbon::create(2017, 3, 20);
        $returned = \BespokeSupport\DateGuesser\DateGuesser::create($obj);
        $this->assertEquals(2017, $returned->year);
        $this->assertEquals(3, $returned->month);
        $this->assertEquals(20, $returned->day);
    }

    public function testNonStandard()
    {
        $returned = \BespokeSupport\DateGuesser\DateGuesser::create('31-12-17');
        $this->assertEquals(2017, $returned->year);
        $this->assertEquals(12, $returned->month);
        $this->assertEquals(31, $returned->day);
    }

    public function testTextual()
    {
        $returned = \BespokeSupport\DateGuesser\DateGuesser::create('last day of January 2008');
        $this->assertEquals(2008, $returned->year);
        $this->assertEquals(01, $returned->month);
        $this->assertEquals(31, $returned->day);
    }

    public function testFormatInvalidNew()
    {
        $returned = \BespokeSupport\DateGuesser\DateGuesser::create('31-12-17 13:59');
        $this->assertNull($returned);
    }

    public function testNewFormat()
    {
        \BespokeSupport\DateGuesser\DateGuesser::$attemptFormatsAdditional[] = 'd-m-y H:i';

        $date = '31-12-17 13:59';

        $expected = Carbon::create(2017, 12, 31, 13, 59, 00);
        $returned = \BespokeSupport\DateGuesser\DateGuesser::create($date);

        if ($expected === null) {
            $this->assertNull($returned);
        } else {
            $this->assertNotNull($returned);
        }

        $this->responseCheck($expected, $returned, $date);
    }

    public function testNewFormatMicro()
    {
        \BespokeSupport\DateGuesser\DateGuesser::$attemptFormatsAdditional[] = 'd-m-y H:i:s.u';

        $date = '31-12-17 13:59:59.01';

        $expected = Carbon::create(2017, 12, 31, 13, 59, 59);
        $returned = \BespokeSupport\DateGuesser\DateGuesser::create($date);

        if ($expected === null) {
            $this->assertNull($returned);
        } else {
            $this->assertNotNull($returned);
        }

        $this->responseCheck($expected, $returned, $date);

        $this->assertEquals(10000, $returned->micro);
    }

    public function testZero()
    {
        $date = '31-12-17';

        $returned = \BespokeSupport\DateGuesser\DateGuesser::create($date);

        $this->assertEquals(0, $returned->hour);


    }
}
