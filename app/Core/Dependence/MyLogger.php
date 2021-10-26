<?php

declare(strict_types=1);
/**
 * 当前继承的方法只修改传参类型限制
 *
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Core\Dependence;

use Hyperf\Logger\Logger;
use Monolog\DateTimeImmutable;
use Throwable;

class MyLogger extends Logger
{
    /**
     * 修改参数类型 $message
     * Adds a log record.
     *
     * @param int $level The logging level
     * @param mixed $message The log message
     * @param mixed[] $context The log context
     * @return bool Whether the record has been processed
     */
    public function addRecord(int $level, $message, array $context = []): bool
    {
        $offset = 0;
        $record = null;

        foreach ($this->handlers as $handler) {
            if ($record === null) {
                // skip creating the record as long as no handler is going to handle it
                if (! $handler->isHandling(['level' => $level])) {
                    continue;
                }

                $levelName = static::getLevelName($level);

                $record = [
                    'message' => $message,
                    'context' => $context,
                    'level' => $level,
                    'level_name' => $levelName,
                    'channel' => $this->name,
                    'datetime' => new DateTimeImmutable($this->microsecondTimestamps, $this->timezone),
                    'extra' => [],
                ];

                try {
                    foreach ($this->processors as $processor) {
                        $record = $processor($record);
                    }
                } catch (Throwable $e) {
                    $this->handleException($e, $record);

                    return true;
                }
            }

            // once the record exists, send it to all handlers as long as the bubbling chain is not interrupted
            try {
                if ($handler->handle($record) === true) {
                    break;
                }
            } catch (Throwable $e) {
                $this->handleException($e, $record);

                return true;
            }
        }

        return $record !== null;
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param mixed $message The log message
     * @param mixed[] $context The log context
     */
    public function error($message, array $context = []): void
    {
        $this->addRecord(static::ERROR, $message, $context);
    }

    /**
     * Adds a log record at an arbitrary level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param mixed $level The log level
     * @param mixed $message The log message
     * @param mixed[] $context The log context
     */
    public function log($level, $message, array $context = []): void
    {
        $level = static::toMonologLevel($level);

        $this->addRecord($level, $message, $context);
    }

    /**
     * Adds a log record at the DEBUG level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param mixed $message The log message
     * @param mixed[] $context The log context
     */
    public function debug($message, array $context = []): void
    {
        $this->addRecord(static::DEBUG, $message, $context);
    }

    /**
     * Adds a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param mixed $message The log message
     * @param mixed[] $context The log context
     */
    public function info($message, array $context = []): void
    {
        $this->addRecord(static::INFO, $message, $context);
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param mixed $message The log message
     * @param mixed[] $context The log context
     */
    public function notice($message, array $context = []): void
    {
        $this->addRecord(static::NOTICE, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param mixed $message The log message
     * @param mixed[] $context The log context
     */
    public function warning($message, array $context = []): void
    {
        $this->addRecord(static::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param mixed $message The log message
     * @param mixed[] $context The log context
     */
    public function critical($message, array $context = []): void
    {
        $this->addRecord(static::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param mixed $message The log message
     * @param mixed[] $context The log context
     */
    public function alert($message, array $context = []): void
    {
        $this->addRecord(static::ALERT, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param mixed $message The log message
     * @param mixed[] $context The log context
     */
    public function emergency($message, array $context = []): void
    {
        $this->addRecord(static::EMERGENCY, $message, $context);
    }
}
