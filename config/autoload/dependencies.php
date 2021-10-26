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
return [
    \Hyperf\Logger\LoggerFactory::class => \App\Core\Dependence\MyLoggerFactory::class, //替换addRecord方法，不序列化(string)日志信息
];
