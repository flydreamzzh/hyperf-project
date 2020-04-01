<?php


namespace App\Core\Helpers;


class DateHelper
{
    /**
     * 返回当前的毫秒时间戳
     * @return float
     */
    public static function msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }

    /**
     * 返回当前的周一
     * @return string
     */
    public static function weekFirstDay()
    {
        $date = new \DateTime();
        $date->modify('this week');
        $first_day_of_week = $date->format('Y-m-d');
        return $first_day_of_week;
    }

    /**
     * 返回当前的周日
     * @return string
     */
    public static function weekLastDay()
    {
        $date = new \DateTime();
        $date->modify('this week +6 days');
        $end_day_of_week = $date->format('Y-m-d');
        return $end_day_of_week;
    }

}