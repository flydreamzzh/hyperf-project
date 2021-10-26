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
    'http' => [
        //跨域请求限制
        \App\Middleware\CorsMiddleware::class,
        //验证请求的数据
        \Hyperf\Validation\Middleware\ValidationMiddleware::class,
        //设置请求开始时间，记录当前用户单例
        \App\Middleware\RequestMiddleware::class,
        //权限验证
        \App\Middleware\AuthMiddleware::class,
    ],
];
