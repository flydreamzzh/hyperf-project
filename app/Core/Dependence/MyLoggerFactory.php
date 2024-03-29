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
namespace App\Core\Dependence;

use App\Core\Dependence\MyLogger as Logger;
use Hyperf\Logger\Exception\InvalidConfigException;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class MyLoggerFactory extends LoggerFactory
{
    public function make($name = 'hyperf', $group = 'default'): LoggerInterface
    {
        $config = $this->config->get('logger');
        if (! isset($config[$group])) {
            throw new InvalidConfigException(sprintf('Logger config[%s] is not defined.', $name));
        }

        $config = $config[$group];
        $handlers = $this->handlers($config);
        $processors = $this->processors($config);

        return make(Logger::class, [
            'name' => $name,
            'handlers' => $handlers,
            'processors' => $processors,
        ]);
    }

    public function get($name = 'hyperf', $group = 'default'): LoggerInterface
    {
        if (isset($this->loggers[$group][$name]) && $this->loggers[$group][$name] instanceof Logger) {
            return $this->loggers[$group][$name];
        }

        return $this->loggers[$group][$name] = $this->make($name, $group);
    }
}
