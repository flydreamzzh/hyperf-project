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
namespace App\Core\Components;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\ApplicationContext;

/**
 * 日志记录类
 * Class Log.
 *
 * @method static void emergency($message, array $context = array());
 * @method static void alert($message, array $context = array());
 * @method static void critical($message, array $context = array());
 * @method static void error($message, array $context = array());
 * @method static void warning($message, array $context = array());
 * @method static void notice($message, array $context = array());
 * @method static void info($message, array $context = array());
 * @method static void debug($message, array $context = array());
 * @method static void log($level, $message, array $context = array());
 */
class Log
{
    /**
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static())->get()->{$method}(...$parameters);
    }

    /**
     * @param string $name
     * @param string $group
     * @return \Psr\Log\LoggerInterface
     */
    public static function get(string $name = 'default', $group = 'default'): \Psr\Log\LoggerInterface
    {
        return ApplicationContext::getContainer()->get(\Hyperf\Logger\LoggerFactory::class)->get($name, $group);
    }

    /**
     * 终端日志.
     * @return \Psr\Log\LoggerInterface
     */
    public static function commandLog(): \Psr\Log\LoggerInterface
    {
        return self::get('command');
    }

    /**
     * 邮箱发送日志.
     * @return \Psr\Log\LoggerInterface
     */
    public static function mailerLog(): \Psr\Log\LoggerInterface
    {
        return self::get('mailer');
    }

    /**
     * 终端提示.
     * @return mixed|StdoutLoggerInterface
     */
    public static function stdLog(): StdoutLoggerInterface
    {
        return ApplicationContext::getContainer()->get(StdoutLoggerInterface::class);
    }
}
