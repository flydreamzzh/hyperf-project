<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Core\Helpers;

class DateHelper
{
    /**
     * 返回当前的毫秒时间戳.
     * @return float
     */
    public static function msectime(): float
    {
        [$msec, $sec] = explode(' ', microtime());
        return (float) sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }

    /**
     * 返回当前的周一
     * @return string
     */
    public static function weekFirstDay(): string
    {
        $date = new \DateTime();
        $date->modify('this week');
        return $date->format('Y-m-d');
    }

    /**
     * 返回当前的周日.
     * @return string
     */
    public static function weekLastDay(): string
    {
        $date = new \DateTime();
        $date->modify('this week +6 days');
        return $date->format('Y-m-d');
    }
}
