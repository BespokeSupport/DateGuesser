<?php

namespace BespokeSupport\DateGuesser;

use Carbon\Carbon;
use DateTimeInterface;
use Exception;
use LogicException;

/**
 * Class DateGuesser.
 */
class DateGuesser
{
    /**
     * @var array
     */
    public static $attemptFormatsAdditional = [];
    /**
     * @var array
     */
    protected static $attemptFormatsBase = [
        'Y-m-d',
        'd F Y',
        'd/F/Y',
        'd/M/Y',
        'd-M-Y',
        'd/m/Y',
        'd-m-Y',
        'dmY',

        // international time
        'Y-m-d H:i',
        'Y-m-d H:i:s',
        'Y-m-d H:i:s.u',

        // international date time
        'd/m/Y H:i',
        'd-m-Y H:i',
        'd/m/Y H:i:s',
        'd-m-Y H:i:s',
        'd/M/Y H:i',
        'd-M-Y H:i',
        'd/M/Y H:i:s',
        'd-M-Y H:i:s',

        'd-m-y',
        'd/m/y',
        'dmy',
        'dny',
        'jny',

        // unix time
        'U',
    ];

    /**
     * DateGuesser constructor.
     */
    public function __construct()
    {
        throw new LogicException('Non functioning Constructor use DateGuesser::create() ');
    }

    /**
     * @param int|string|DateTimeInterface|null $time
     *
     * @return Carbon
     */
    public static function create($time)
    {
        if ($time instanceof DateTimeInterface) {
            return new Carbon($time);
        }

        if (!$time) {
            return null;
        }

        if (preg_match('#^\W+$#', $time)) {
            return null;
        }

        if (is_numeric($time) && $time < 100) {
            return null;
        }

        $time = (string) $time;

        $obj = null;

        foreach (array_merge(self::$attemptFormatsBase, self::$attemptFormatsAdditional) as $format) {
            if ($format === 'U' && strlen($time) < 8) {
                continue;
            }

            try {
                $obj = Carbon::createFromFormat($format, $time);
                $errors = Carbon::getLastErrors();
            } catch (Exception $exception) {
                continue;
            }

            if ($obj && !$errors['error_count'] && !$errors['warning_count']) {
                if (strpos($format, 'Y') !== false && strlen($obj->year) !== 4) {
                    continue;
                }

                if (strpos($format, 'H') === false) {
                    $obj = self::setTimeToZero($obj);
                }

                return $obj;
            }
        }

        if (is_string($time) && strlen($time) < 4) {
            return null;
        }

        try {
            $obj = new Carbon($time);
            if (strlen($obj->year) === 4) {
                // prevent 30-01-17 to become 2030-01-17
                if (preg_match('#^\d{2}.\d{2}.\d{2}#', $time) && strpos($time, substr($obj->year, -2, 2)) === 0) {
                    return null;
                }
            }
        } catch (Exception $exception) {
            return null;
        }

        if (is_string($time) && strlen($time) < 10) {
            $obj = self::setTimeToZero($obj);
        }

        return $obj;
    }

    /**
     * @param Carbon $obj
     * @return mixed
     */
    protected static function setTimeToZero(Carbon $obj)
    {
        return $obj->setTime(0, 0);
    }
}
