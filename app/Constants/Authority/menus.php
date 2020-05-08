<?php
/**
 * 值：200 ~ 300
 * 系统菜单初始化
 */

$menus = [
    ['id' => 201, 'code' => 'rbac', 'name' => '权限管理', 'url' => 'rbac', 'icon' => 'el-icon-lollipop', 'component' => '', 'only_master' => 1, 'module' => '/rbac', 'sort' => 0,
        'children' => [
            ['id' => 202, 'code' => 'manager', 'name' => '管理员列表', 'url' => 'manager', 'icon' => 'el-icon-user', 'component' => '/rbac/manager', 'only_master' => 1, 'module' => '/user', 'sort' => 1],
            ['id' => 203, 'code' => 'permission', 'name' => '角色权限', 'url' => 'permission', 'icon' => 'el-icon-aim', 'component' => '/rbac/permission', 'only_master' => 1, 'module' => '/rbac/role', 'sort' => 2],
        ]
    ],
    ['id' => 211, 'code' => 'system', 'name' => '系统管理', 'url' => 'system', 'icon' => 'el-icon-setting', 'component' => '', 'only_master' => 1, 'module' => '', 'sort' => 0,
        'children' => [
            ['id' => 212, 'code' => 'alarm', 'name' => '告警管理', 'url' => 'alarm', 'icon' => 'el-icon-warning-outline', 'component' => '/system/alarm', 'only_master' => 1, 'module' => '/system/alarm', 'sort' => 1],
        ]
    ],
];

function getMenus($menus, $parentId = null)
{
    $data = [];
    foreach ($menus as $menu) {
        $menu['parent_id'] = $parentId;
        if (!empty($menu['children']) && is_array(current($menu['children']))) {
            $data = \App\Core\Helpers\ArrayHelper::merge((array)getMenus($menu['children'], $menu['id']), $data);
            unset($menu['children']);
            $data[] = $menu;
        } else {
            $data[] = $menu;
        }
    }
    return $data;
}

return getMenus($menus);
