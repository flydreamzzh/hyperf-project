<?php

declare (strict_types=1);

namespace App\Model;

use App\Core\Helpers\ArrayHelper;
use App\Model\Rbac\RbacPermission;
use App\Model\Rbac\RbacUserRole;

/**
 * @property int $id
 * @property string $code 编号
 * @property string $name
 * @property string $url 名称
 * @property string $icon 图标
 * @property string $component 组件
 * @property int $only_master 仅超管可见
 * @property string $module 权限模块，适配超管可见
 * @property int $sort 序号
 * @property int $parent_id 父类
 */
class Menu extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menus';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['code', 'name', 'url', 'icon', 'component', 'only_master', 'module', 'sort', 'parent_id'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'only_master' => 'integer', 'sort' => 'integer', 'parent_id' => 'integer'];

    public $operators = false;

    public $timestamps = false;

    /**
     * 额外显示映射的字段名称
     * @var array
     */
    public static $default_mapped = ['id' => 'id', 'label' => 'name'];

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['code', 'name'], 'required'],
            [['only_master', 'sort', 'parent_id'], 'integer'],
            [['code', 'name'], 'string', 'max:40' => 40],
            [['module'], 'string', 'max:60' => 60],
            [['url', 'icon', 'component'], 'max:255'],
            [['code'], 'unique_c'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => '编号',
            'name' => '名称',
            'url' => '路由',
            'icon' => '图标',
            'component' => '组件',
            'only_master' => '是否仅超管可见',
            'module' => '权限模块范围',
            'sort' => '序号',
            'parent_id' => 'Parent ID',
        ];
    }

    /**
     * 获取所有菜单树
     * @param array $other_columns 额外映射的字段
     * @return array
     */
    public static function getMenusTree($other_columns = [])
    {
        $columns = array_merge($other_columns, ['menus.*']);
        $items = self::find()->select($columns)->whereNull('parent_id')->orderBy(['sort' => SORT_ASC])->get()->toArray();

        foreach ($items as $key => $item) {
            $items[$key]['children'] = self::getMenuChildren($item['id'], $other_columns);
        }
        return $items;
    }

    /**
     * 获取某棵菜单树
     * @param string $menu_id
     * @param array $other_columns 额外映射的字段
     * @return array|null
     */
    public static function getMenuTree($menu_id, $other_columns = [])
    {
        $columns = array_merge($other_columns, ['menus.*']);
        $menu = self::find()->select($columns)->where(['id' => $menu_id])->first();
        if ($menu) {
            $menu =$menu->toArray();
            $menu['children'] = self::getMenuChildren($menu_id, $other_columns);
        }
        return $menu;
    }

    /**
     * 获取某菜单的子孙菜单
     * @param string $menu_id
     * @param array $other_columns 额外映射的字段
     * @return array
     */
    public static function getMenuChildren($menu_id, $other_columns = [])
    {
        $columns = array_merge($other_columns, ['menus.*']);
        $items = self::find()->select($columns)->where(['parent_id' => $menu_id])->orderBy(['sort' => SORT_ASC])->get()->toArray();

        if ($items) {
            foreach ($items as $key => $item) {
                if ($children = self::getMenuChildren($item['id'], $other_columns)) {
                    $items[$key]['children'] = $children;
                }
            }
        }
        return $items;
    }

    /**
     * 获取菜单的父类
     * @param $menu_id string
     * @return array|null
     */
    public static function getParentTree($menu_id)
    {
        $menu = self::find()->where(['id' => $menu_id])->first()->toArray();
        if ($menu['parent_id']) {
            $parent = self::find()->where(['id' => $menu['parent_id']])->first()->toArray();
            if ($parent['parent_id']) {
                $parent['parent'] = self::getParentList($parent['id']);
            }
            return $parent;
        }
        return null;
    }

    /**
     * 获取菜单的所有父类，由小到大
     * @param $menu_id string
     * @param $small2big bool 是否由小到大
     * @return array
     */
    public static function getParentList($menu_id, $small2big = true)
    {
        $data = [];
        $menu = self::find()->where(['id' => $menu_id])->first()->toArray();
        if ($menu['parent_id']) {
            $parent = self::find()->where(['id' => $menu['parent_id']])->first()->toArray();
            $data[] = $parent;
            if ($parent['parent_id']) {
                if ($small2big) {
                    array_push($data, self::getParentList($parent['id'], $small2big)[0]);
                } else {
                    array_unshift($data, self::getParentList($parent['id'], $small2big)[0]);
                }
            }
        }
        return $data;
    }

    /**
     * 获取菜单权限列表
     * @param string $user_id
     * @return array
     */
    public static function getMenusByUser($user_id)
    {
        $menus = self::find()->whereNull('parent_id')->orderBy('sort')->get()->toArray();
        $isMaster = RbacUserRole::isMaster($user_id);
        foreach ($menus as $key => $menu) {
            if ($menu['only_master'] && !$isMaster) {
                unset($menus[$key]);
                continue;
            }
            $children = self::getMenuChildrenByUser($menu['id'], $user_id);

            if ($children['has']) {
                if ($children['children']) {
                    $menus[$key]['children'] = $children['children'];
                } else {
                    unset($menus[$key]);
                }
            } else {
                if (!RbacPermission::canMenu($user_id, $menu['id'])) {
                    unset($menus[$key]);
                }
            }
        }
        return array_values($menus);
    }

    /**
     * 递归菜单权限
     * @param string $menu_id
     * @param string $user_id
     * @return array
     */
    public static function getMenuChildrenByUser($menu_id, $user_id)
    {
        $menus = self::find()->where(['parent_id' => $menu_id])->orderBy('sort')->get()->toArray();
        if ($menus) {
            $isMaster = RbacUserRole::isMaster($user_id);
            foreach ($menus as $key => $menu) {
                if ($menu['only_master'] && !$isMaster) {
                    unset($menus[$key]);
                    continue;
                }
                $children = self::getMenuChildrenByUser($menu['id'], $user_id);
                if ($children['has']) {
                    if ($children['children']) {
                        $menus[$key]['children'] = $children['children'];
                    } else {
                        unset($menus[$key]);
                    }
                } else {
                    if (!RbacPermission::canMenu($user_id, $menu['id'])) {
                        unset($menus[$key]);
                    }
                }
            }
            return ['has' => true, 'children' => array_values($menus)];
        }
        return ['has' => false];
    }

    /**
     * 获取一级菜单、供创建选择 父级
     * @return array
     */
    public static function getCanSelectMenus()
    {
        $menus = self::find()->whereNull('parent_id')->orderBy('sort')->get()->toArray();
        $data = $menus;
        foreach ($menus as $menu) {
            $children = self::find()->where(['parent_id' => $menu['id']])->orderBy('sort')->get()->toArray();
            $data = ArrayHelper::merge($children, $data);
        }
        return $data;
    }
}