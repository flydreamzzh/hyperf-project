<?php
/**
 * 值：1 ~ 100000
 * 用户相关权限初始化
 */

return [
    ['id' => 1, 'name' => '添加管理员', 'menu_id' => 202, 'description' => '', 'system' => 1, 'home_page' => 0,
        'routes' => [
            ['route' => '/manager/add', 'system' => 1, 'home_route' => 0]
        ],
    ],
];
