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
    ['id' => 1, 'name' => '添加管理员', 'menu_id' => 202, 'description' => '', 'system' => 1, 'home_page' => 0,
        'routes' => [
            ['route' => '/manager/add', 'system' => 1, 'home_route' => 0],
        ],
    ],
];
