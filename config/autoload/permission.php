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
    //，没有归到权限的路由，默认允许登录
    'defaultAccess' => true,

    //不用登录也能 访问的地址
    'allowActions' => [
        '/',
        '/test/*',
        '/site/*',
        '/user/resetPassword',
    ],

    //以登录为前提条件，所允许的地址
    'loginActions' => [
        '/user/info',
    ],
];
