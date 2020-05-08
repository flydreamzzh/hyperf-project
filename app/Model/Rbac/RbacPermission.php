<?php

declare (strict_types=1);

namespace App\Model\Rbac;

use App\Core\Components\Builder;
use App\Core\Helpers\ArrayHelper;
use App\Model\Menu;
use App\Model\Model;
use Hyperf\DbConnection\Db;

/**
 * @property int $id
 * @property string $name 权限名
 * @property int $menu_id 隶属菜单
 * @property string $description 简介
 * @property int $system 是否系统，1是
 * @property int $home_page 是否首页权限，1是
 * @property array $routes 路由
 */
class RbacPermission extends Model
{
    public $operators = false;

    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rbac_permissions';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'menu_id', 'description', 'system', 'home_page', 'routes'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'menu_id' => 'integer', 'system' => 'integer', 'home_page' => 'integer'];


    /**
     * 权限模块,不归于用户权限分配范围内
     * @var string
     */
    public static $rbac_module = "rbac";

    /**
     * @var array 权限路由，表单辅助保存
     */
    public $routes = [];

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name', 'menu_id'], 'required'],
            [['menu_id', 'system', 'home_page'], 'integer'],
            [['name'], 'max:40'],
            [['description'], 'max:255'],
//            [['menu_id'], 'exist', 'skipOnError' => true, 'targetClass' => Menu::className(), 'targetAttribute' => ['menu_id' => 'id']],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => '权限名称',
            'menu_id' => '菜单主键',
            'description' => '描述',
            'system' => '系统权限，1是',
            'home_page' => '菜单页权限',
        ];
    }


    /**
     * 保存权限及其路由
     * @return bool
     */
    public function saveWithRoutes()
    {
        $this->routes = array_filter($this->routes);
        if (!$this->routes) {
            $this->addError('routes', '权限路由不能为空');
            return false;
        }
        Db::beginTransaction();
        try {
            if ($this->save()) {
                $routes = RbacPermissionRoute::findAll(['permission_id' => $this->id, 'system' => 0]);
                $oldIds = $routes ? ArrayHelper::getColumn($routes, 'route') : [];
                $delete_routes = array_diff($oldIds, $this->routes);
                $create_routes = array_diff($this->routes, $oldIds);
                $delete_flag = true;
                $create_flag = true;
                if ($delete_routes) {
                    $delete_flag = RbacPermissionRoute::deleteAll(['permission_id' => $this->id, 'route' => $delete_routes]) ? true : false; //清除不再拥有的权限
                }
                if ($create_routes) {
                    $data = [];
                    foreach ($create_routes as $route) {
                        $data[] = array_combine(['route', 'permission_id'], [$route, $this->id]);
                    }
                    $create_flag = Db::table(RbacPermissionRoute::tableName())->insert($data);//添加新的权限
                    if (!$create_flag) {
                        $this->addError('routes', '路由已存在');
                    }
                }
                if ($delete_flag && $create_flag) {
                    Db::commit();
                    return true;
                }
            }
            Db::rollBack();
            return false;
        } catch (\Exception $exception) {
            Db::rollBack();
            if (preg_match('/UNIQUE/i', $exception->getMessage())) {
                $this->addError('routes', '路由已被占用');
            } else {
                $this->addError('routes', '保存权限失败.' . $exception->getMessage());
            }
            return false;
        }
    }

    /**
     * 规则：
     *  例子：/admin/assignment/index
     *      先直接查找完整路径，若发现此路由已经配置了权限，则直接判断该用户是否拥有此权限，拥有则通过，相反则不通过；
     *      若此路由没有被配置权限，则查找【/admin/assignment/*】路由是否被配置权限，若被配置，按照以上规则判断；
     *      以此类推，上述路由都不存在，则最终查找【/admin/*】。
     * 判断某用户是否能使用某路由权限
     * @param string $user_id
     * @param string $route
     * @param array $except 不用检测权限的
     * @param boolean $defaultAccess 不存在权限的路由  是否 直接放通。
     * @return bool
     */
    public static function can($user_id, $route, $except = [], $defaultAccess = false)
    {
        if (config('user.administrator_id') == $user_id || RbacUserRole::isMaster($user_id)) {
            return true;
        }

        if (self::isExcept($route, $except)) {
            return true;
        }

        //权限配置仅在用户管理员级别才能进入
        $masterModules = Menu::find()->select(['module'])->where(['only_master' => 1])->whereRaw('module is not null')->get()->toArray();
        $masterModules = ArrayHelper::getColumn($masterModules, 'module');
        $masterModules = array_filter($masterModules);
        if (!RbacUserRole::isMaster($user_id)) {
            foreach ($masterModules as $masterModule) {
                $match = array_filter(explode('/', $masterModule));
                $matchModule = implode('/', $match);
                if (strpos($route, "/{$matchModule}") === 0) {
                    return false;
                }
            }
        }

        $permission = self::getPermissionByRoute($route);
        if ($permission) {
            return RbacUserRole::isHasPermissionForUser($user_id, $permission->id);
        } else {
            $data = explode('/', $route);
            $data = array_values(array_filter($data));
            for ($i = count($data) - 1; $i > 0; $i--) {
                $arr = [""];
                for ($j = 0; $j < $i; $j++) {
                    $arr[] = $data[$j];
                }
                $arr[] = "*";
                $url = implode('/', $arr);
                $permission = self::getPermissionByRoute($url);
                if ($permission) {
                    return RbacUserRole::isHasPermissionForUser($user_id, $permission->id);
                }
            }
        }
        return $defaultAccess;
    }

    /**
     * 判断路由是否直接通过权限
     * @param string $route
     * @param array $except
     * @return bool
     */
    public static function isExcept($route, $except)
    {
        if (!$except) {
            return false;
        }
        if (!in_array($route, $except)) {
            $data = explode('/', $route);
            $data = array_values(array_filter($data));
            for ($i = count($data) - 1; $i > 0; $i--) {
                $arr = [""];
                for ($j = 0; $j < $i; $j++) {
                    $arr[] = $data[$j];
                }
                $arr[] = "*";
                $url = implode('/', $arr);
                if (in_array($url, $except)) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }


    /**
     * 用户能否使用某菜单
     * @param string $user_id
     * @param string $menu_id
     * @return bool
     */
    public static function canMenu($user_id, $menu_id)
    {
        if (config('user.administrator_id') == $user_id || RbacUserRole::isMaster($user_id)) {
            return true;
        }
        $menu = Menu::findOne($menu_id);
        if ($menu && $menu->only_master && !RbacUserRole::isMaster($user_id)) {
            return false;
        }
        if ($permission = self::findOne(['menu_id' => $menu_id, 'home_page' => 1])) {
            return RbacUserRole::isHasPermissionForUser($user_id, $permission->id);
        }
        return true;
    }

    /**
     * 获取某路由的权限
     * @param string $route
     * @return RbacPermission
     */
    public static function getPermissionByRoute($route)
    {
        /** @var RbacPermission $permission */
        $permission = self::find()->alias('permission')->where(['permission_routes.route' => $route])
            ->leftJoin(['permission_routes' => RbacPermissionRoute::tableName()], 'permission_routes.permission_id', '=', 'permission.id')
            ->first();
        return $permission;
    }

    /**
     * 获取菜单权限列表
     * @param boolean $only_master 不显示仅超管可看的菜单，主要用作权限分配界面
     * @return array
     */
    public static function getMenuPermissions($only_master = false)
    {
        if (!$only_master) {
            $menus = Menu::find()->whereNull('parent_id')->orderBy('sort')->get();
        } else {
            $menus = Menu::find()->whereNull('parent_id')->where(['only_master' => 0])->orderBy('sort')->get()->toArray();
        }

        foreach ($menus as $i => $menu) {
            //当前菜单主页权限（就是用户能否拥有该菜单）
            $menuPermission = self::find()->where(['menu_id' => $menu['id'], 'home_page' => 1])->orderBy(['id' => SORT_ASC])->first();
            if ($menuPermission) {
                $menus[$i]['menuPermission'] = $menuPermission->toArray(); //模块主页权限
            }
            //隶属当前菜单的权限（增删查。。。）
            $permissions = self::find()->where(['menu_id' => $menu['id'], 'home_page' => 0])->orderBy(['system' => SORT_ASC, 'id' => SORT_ASC])->get()->toArray();
            $menus[$i]['permissions'] = $permissions;

            //子菜单
            if ($children = self::getMenuChildren($menu['id'], $only_master)) {
                $menus[$i]['children'] = $children;
            } else {
                if (!$menuPermission) {
                    unset($menus[$i]);
                }
            }
        }
        return array_values($menus);
    }

    /**
     * 递归菜单权限
     * @param string $menu_id
     * @param boolean $only_master 不显示仅超管可看的菜单，主要用作权限分配界面
     * @return array
     */
    public static function getMenuChildren($menu_id, $only_master = false)
    {
        if (!$only_master) {
            $menus = Menu::find()->where(['parent_id' => $menu_id])->orderBy('sort')->get()->toArray();
        } else {
            $menus = Menu::find()->where(['parent_id' => $menu_id])->where(['only_master' => 0])->orderBy('sort')->get()->toArray();
        }
        if ($menus) {
            foreach ($menus as $i => $menu) {
                //当前菜单主页权限（就是用户能否拥有该菜单）
                $menuPermission = self::find()->where(['menu_id' => $menu['id'], 'home_page' => 1])->orderBy(['id' => SORT_ASC])->first();
                if ($menuPermission) {
                    $menus[$i]['menuPermission'] = $menuPermission->toArray(); //模块主页权限
                }
                //隶属当前菜单的权限（增删查。。。）
                $permissions = self::find()->where(['menu_id' => $menu['id'], 'home_page' => 0])->orderBy(['system' => SORT_ASC, 'id' => SORT_ASC])->get()->toArray();
                $menus[$i]['permissions'] = $permissions;

                //子菜单
                if ($children = self::getMenuChildren($menu['id'], $only_master)) {
                    $menus[$i]['children'] = $children;
                } else {
                    if (!$menuPermission) {
                        unset($menus[$i]);
                    }
                }
            }
        }
        return array_values($menus);
    }

    /**
     * 获取所有菜单的权限
     * @param string $menuId
     * @return static[]
     */
    public static function getPermissionsByMenu($menuId)
    {
        $permissions = self::findAll(['menu_id' => $menuId, 'home_page' => 0]);
        return $permissions;
    }

    /**
     * 初始化菜单首页权限，未设置真正路由权限
     */
    public static function initPermissions()
    {
        /** @var Menu $menus 获取所有底层菜单 */
        $menus = Menu::find()->alias('children')->whereNotExists(function (Builder $query) {
            $query->whereColumn('menus.parent_id', '=', 'children.id')->from("menus");
        })->get()->toArray();
        /** @var Menu $menu */
        foreach ($menus as $menu) {
            //菜单权限初始化
            $permissions = self::find()->where(['menu_id' => $menu['id'], 'home_page' => 1])->orderBy(['id' => SORT_ASC])->get()->toArray();
            if ($permissions) {
                $permission = $permissions[0];
                if (count($permissions) > 1) {
                    RbacPermission::deleteAll([["menu_id" => $menu['id']], ['id', '>', $permission['id']], ['home_page' => 1]]); //只需要第一个先被创建的权限，其他删除
                }
            } else {
                //100000开始为菜单首页权限
                $maxPermissionId = RbacPermission::find()->where('id', '>', 100000)->orderBy(['id' => SORT_DESC])->first();
                $maxId = $maxPermissionId ? $maxPermissionId->id + 1 : 100001;
                $permission = new RbacPermission();
                $permission->id = $maxId;
                $permission->menu_id = $menu['id'];
                $permission->name = $menu['name'];
                $permission->home_page = 1;
                $permission->system = 1;
                $permission->description = $menu['name'];
                $permission->save();
            }
        }
    }
}