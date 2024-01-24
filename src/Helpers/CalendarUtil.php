<?php

namespace UncleCheese\EventCalendar\Helpers;

use Carbon\Carbon;
use UncleCheese\EventCalendar\Models\CalendarDateTime;
use UncleCheese\EventCalendar\Pages\Calendar;

class CalendarUtil
{
    const ONE_DAY = "OneDay";
    const SAME_MONTH_SAME_YEAR = "SameMonthSameYear";
    const DIFF_MONTH_SAME_YEAR = "DiffMonthSameYear";
    const DIFF_MONTH_DIFF_YEAR = "DiffMonthDiffYear";
    const ONE_DAY_HEADER = "OneDayHeader";
    const MONTH_HEADER = "MonthHeader";
    const YEAR_HEADER = "YearHeader";

    /**
     * @return array
     */
    private static $format_character_placeholders = [
        '$StartDayNameShort',
        '$StartDayNameLong',
        '$StartDayNumberShort',
        '$StartDayNumberLong',
        '$StartDaySuffix',
        '$StartMonthNumberShort',
        '$StartMonthNumberLong',
        '$StartMonthNameShort',
        '$StartMonthNameLong',
        '$StartYearShort',
        '$StartYearLong',
        '$EndDayNameShort',
        '$EndDayNameLong',
        '$EndDayNumberShort',
        '$EndDayNumberLong',
        '$EndDaySuffix',
        '$EndMonthNumberShort',
        '$EndMonthNumberLong',
        '$EndMonthNameShort',
        '$EndMonthNameLong',
        '$EndYearShort',
        '$EndYearLong'
    ];

    /**
     * @return array
     */
    public static function format_character_replacements($start, $end)
    {
        $start = new \DateTime("@$start");

        // Check if $end is null before creating a DateTime object
        if ($end === null) {
            return [
                $start->format('D'),
                $start->format('l'),
                date('j', $start->getTimestamp()),
                date('d', $start->getTimestamp()),
                date('S', $start->getTimestamp()),
                date('n', $start->getTimestamp()),
                date('m', $start->getTimestamp()),
                $start->format('M'),
                $start->format('F'),
                date('y', $start->getTimestamp()),
                date('Y', $start->getTimestamp()),
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ];
        }

        $end = new \DateTime("@$end");

        return [
            $start->format('D'),
            $start->format('l'),
            date('j', $start->getTimestamp()),
            date('d', $start->getTimestamp()),
            date('S', $start->getTimestamp()),
            date('n', $start->getTimestamp()),
            date('m', $start->getTimestamp()),
            $start->format('M'),
            $start->format('F'),
            date('y', $start->getTimestamp()),
            date('Y', $start->getTimestamp()),
            $end->format('D'),
            $end->format('l'),
            date('j', $end->getTimestamp()),
            date('d', $end->getTimestamp()),
            date('S', $end->getTimestamp()),
            date('n', $end->getTimestamp()),
            date('m', $end->getTimestamp()),
            $end->format('M'),
            $end->format('F'),
            date('y', $end->getTimestamp()),
            date('Y', $end->getTimestamp()),
        ];
    }

    /**
     * @return string
     */
    public static function localize($start, $end, $key)
    {
        global $customDateTemplates;
        if (is_array($customDateTemplates) && isset($customDateTemplates[$key])) {
            $template = $customDateTemplates[$key];
        } else {
            $template = _t(Calendar::class.".$key", $key);
        }

        return str_replace(
            self::$format_character_placeholders,
            self::format_character_replacements($start, $end),
            $template
        );
    }

    /**
     * @return string
     */
    public static function get_date_from_string($str)
    {
        $str = str_replace('-', '', $str);
        if (is_numeric($str)) {
            $missing = (8 - strlen($str));
            if ($missing > 0) {
                while ($missing > 0) {
                    $str .= "01";
                    $missing -= 2;
                }
            }
            return substr($str, 0, 4) . "-" . substr($str, 4, 2) . "-" . substr($str, 6, 2);
        }

        return date('Y-m-d');
    }

    /**
     * @return array|null
     */
    public static function get_date_string($startDate, $endDate)
    {
        $strStartDate = null;
        $strEndDate = null;

        $start = new \DateTime($startDate);

        $startYear = $start->format("Y");
        $startMonth = $start->format("m");

        // Invalid date. Get me out of here!
        if ($start === false) {
            return;
        }

        // Check if $end is null before creating a DateTime object
        if ($endDate === null) {
            $key = self::ONE_DAY;
            $dateString = self::localize($start->getTimestamp(), null, $key);
            return [$dateString, ""];
        }

        $end = new \DateTime($endDate);
        $endYear = $end->format("Y");
        $endMonth = $end->format("m");

        // Only one day long!
        if ($start == $end) {
            $key = self::ONE_DAY;
        } elseif ($startYear == $endYear) {
            $key = ($startMonth == $endMonth) ? self::SAME_MONTH_SAME_YEAR : self::DIFF_MONTH_SAME_YEAR;
        } else {
            $key = self::DIFF_MONTH_DIFF_YEAR;
        }
        $dateString = self::localize($start->getTimestamp(), $end->getTimestamp(), $key);
        $break = strpos($dateString, '$End');
        if ($break !== false) {
            $strStartDate = substr($dateString, 0, $break);
            $strEndDate = substr($dateString, $break+1, strlen($dateString) - strlen($strStartDate));
            return [$strStartDate, $strEndDate];
        }

        return [$dateString, ""];
    }

    /**
     * @return string
     */
    public static function microformat($date, $time, $offset = null)
    {
        if (!$date) {
            return "";
        }
        $ts = strtotime($date . " " . $time);
        if ($ts < 1) {
            return "";
        }
        $ret = date('c', $ts); // ISO 8601 datetime
        if ($offset) {
            // Swap out timezine with specified $offset
            $ret = preg_replace('/((\+)|(-))[\d:]*$/', $offset, $ret);
        }
        return $ret;
    }

    /**
     * @return array
     */
    public static function get_months_map($key = '%b')
    {
        return [
            '01' => (new \DateTime('2000-01-01'))->format($key),
            '02' => (new \DateTime('2000-02-01'))->format($key),
            '03' => (new \DateTime('2000-03-01'))->format($key),
            '04' => (new \DateTime('2000-04-01'))->format($key),
            '05' => (new \DateTime('2000-05-01'))->format($key),
            '06' => (new \DateTime('2000-06-01'))->format($key),
            '07' => (new \DateTime('2000-07-01'))->format($key),
            '08' => (new \DateTime('2000-08-01'))->format($key),
            '09' => (new \DateTime('2000-09-01'))->format($key),
            '10' => (new \DateTime('2000-10-01'))->format($key),
            '11' => (new \DateTime('2000-11-01'))->format($key),
            '12' => (new \DateTime('2000-12-01'))->format($key)
        ];
    }

    /**
     * @return string
     */
    public static function get_date_format()
    {
        if ($dateFormat = CalendarDateTime::config()->date_format_override) {
            return $dateFormat;
        }
        return _t(__CLASS__.'.DATEFORMAT', 'mdy');
    }

    /**
     * @return string
     */
    public static function get_time_format()
    {
        if ($timeFormat = CalendarDateTime::config()->time_format_override) {
            return $timeFormat;
        }
        return _t(__CLASS__.'.TIMEFORMAT', '24');
    }

    /**
     * @return int
     */
    public static function get_first_day_of_week()
    {
        $result = strtolower(_t(__CLASS__.'.FIRSTDAYOFWEEK', 'monday'));
        return ($result == "monday") ? Carbon::MONDAY : Carbon::SUNDAY;
    }

    public static function date_sort(&$data)
    {
        uasort($data, [self::class, "date_sort_callback"]);
    }

    /**
     * Callback used by column_sort
     */
    public static function date_sort_callback($a, $b)
    {
        if ($a->StartDate == $b->StartDate) {
            if ($a->StartTime == $b->StartTime) {
                return 0;
            } elseif (strtotime($a->StartTime) > strtotime($b->StartTime)) {
                return 1;
            }
            return -1;
        } elseif (strtotime($a->StartDate) > strtotime($b->StartDate)) {
            return 1;
        }
        return -1;
    }

    /**
     * @return string
     */
    public static function format_time($timeObj)
    {
        return self::get_time_format() == '24'
            ? $timeObj->Format('HH:mm')
            : $timeObj->Nice();
    }
}
