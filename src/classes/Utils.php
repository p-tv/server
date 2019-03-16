<?php

namespace ptv;


class Utils {

    /**
     * Gets the current time in YYYY-MM-DD HH:ii:ss format
     * @return string
     */
    public static function GetCurrentDateTimeString(): string {
        return date('Y-m-d H:i:s');
    }

    /**
     * Gets a readable duration string.e.g 1 hour, 23 minutes, 10 seconds.
     *
     * @param int $seconds Number of seconds
     * @return string
     */
    public static function GetReadableDuration(int $seconds): string {
        $ret = '';
        $hourComponent = 0;
        $minuteComponent = 0;
        while ($seconds >= 3600) {
            $hourComponent++;
            $seconds -= 3600;
        }
        while ($seconds >= 60) {
            $minuteComponent++;
            $seconds -= 60;
        }

        if ($hourComponent > 0) {
            $ret .= $hourComponent . ' hour';
            if ($hourComponent > 1) {
                $ret .= 's';
            }
            $ret .= ' ';
        }

        if ($minuteComponent > 0) {
            $ret .= $minuteComponent . ' minute';
            if ($minuteComponent > 1) {
                $ret .= 's';
            }
            $ret .= ' ';
        }
        if ($seconds > 0) {
            $ret .= $seconds . ' second';
            if ($seconds > 1) {
                $ret .= 's';
            }
        }

        return trim($ret);
    }

    /**
     * Converts unix time to a datetime string in YYYY-MM-DD HH:ii:ss format.
     *
     * @param int $unixTime Time to convert
     * @return string
     */
    public static function UnixTimeToDateTime(int $unixTime): string {
        return date("Y-m-d H:i:s", $unixTime);
    }

    /**
     * Returns the day of week from a unix time in upper case. e.g. MON,TUE,WED,THU,FRI,SAT,SUN
     * @param int $unixTime
     * @return string
     */
    public static function GetDayOfWeek(int $unixTime): string {
        return strtoupper(date('D', $unixTime));
    }

    /**
     * Returns if this unix time is a weekend
     *
     * @param int $unixTime
     * @return string
     */
    public static function IsWeekend(int $unixTime): string {
        $dayName = Utils::GetDayOfWeek($unixTime);
        if ($dayName === 'SAT' || $dayName === 'SUN') {
            return true;
        }
        return false;
    }

    /**
     * Returns if this unix time is a weekend
     *
     * @param int $unixTime
     * @return string
     */
    public static function IsWeekDay(int $unixTime): string {
        return !Utils::IsWeekend($unixTime);
    }

    /**
     * Makes a GUID of random data
     * @return string
     * @throws \Exception
     */
    public static function MakeGUID() {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

}