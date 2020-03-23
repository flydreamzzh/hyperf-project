<?php
namespace App\Core\Components;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\ApplicationContext;

/**
 * 日志记录类
 * Class Log
 * @package App\Base
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
     * @param string $name
     * @param string $group
     * @return \Psr\Log\LoggerInterface
     */
    public static function get(string $name = 'default', $group = 'default')
    {
        return ApplicationContext::getContainer()->get(\Hyperf\Logger\LoggerFactory::class)->get($name, $group);
    }

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
     * 终端提示
     * @return StdoutLoggerInterface|mixed
     */
    public static function stdLog()
    {
        return ApplicationContext::getContainer()->get(StdoutLoggerInterface::class);
    }
}