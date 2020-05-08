<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

return [
    'handler' => [
        'http' => [
            //请求数据验证失败的错误管理
            \App\Exception\Handler\ValidationExceptionHandler::class,
            //全局异常处理
            App\Exception\Handler\AppExceptionHandler::class,
        ],
    ],
];
