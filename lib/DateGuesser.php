<?php

namespace BespokeSupport\DateGuesser;

use Cake\Chronos\Chronos;
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
     * @return Carbon|Chronos|DateTimeInterface
     */
    public static function create($time)
    {
        if ($time instanceof DateTimeInterface) {
            return self::createFromClass($time);
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

        $time = (string)$time;

        $obj = null;

        foreach (array_merge(self::$attemptFormatsBase, self::$attemptFormatsAdditional) as $format) {
            if ($format === 'U' && strlen($time) < 8) {
                continue;
            }

            try {
                $obj = self::createFromFormat($format, $time);
                $errors = self::errorsFromLastAttempt();
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
            $obj = self::createFromClass($time);

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
     * @param Chronos|Carbon|DateTimeInterface $obj
     * @return mixed
     */
    protected static function setTimeToZero(DateTimeInterface $obj)
    {
        return $obj->setTime(0, 0);
    }

    /**
     * @param $time
     * @return Chronos|Carbon
     */
    protected static function createFromClass($time)
    {
        switch (true) {
            case (class_exists(Chronos::class)):
                return new Chronos($time);
            case (class_exists(Carbon::class)):
                try {
                    return new Carbon($time);
                } catch (Exception $e) {
                }
        }

        throw new \LogicException('Carbon / Chronos required');
    }

    /**
     * @param $format
     * @param $time
     * @return bool|Chronos|\DateTime
     */
    protected static function createFromFormat($format, $time)
    {
        switch (true) {
            case (class_exists(Chronos::class)):
                return Chronos::createFromFormat($format, $time);
            case (class_exists(Carbon::class)):
                $obj = Carbon::createFromFormat($format, $time);
                if ($obj instanceof Carbon) {
                    return $obj;
                }
        }

        throw new \LogicException('Carbon / Chronos required');
    }

    /**
     * @return array
     */
    protected static function errorsFromLastAttempt()
    {
        switch (true) {
            case (class_exists(Chronos::class)):
                return Chronos::getLastErrors();
            case (class_exists(Carbon::class)):
                return Carbon::getLastErrors();
        }

        throw new \LogicException('Carbon / Chronos required');
    }
}
