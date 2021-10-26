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
namespace App\Command;

use App\Core\BaseCommand;
use App\Core\Helpers\ArrayHelper;
use App\Model\Menu;
use App\Model\Rbac\RbacPermission;
use App\Model\Rbac\RbacPermissionRoute;
use Hyperf\Command\Annotation\Command;
use Hyperf\DbConnection\Db;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @Command
 */
class InitCommand extends BaseCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * 菜单数据.
     * @var array
     */
    private $menuPath = [
        'app/Constants/Authority/menus.php',
    ];

    private $permissionPath = [
        'app/Constants/Authority/Permission/*\.php',
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('init');
    }

    public function handle()
    {
        $argument = $this->input->getArgument('type');
        switch ($argument) {
            case 'menu':
                $this->initMenus();
                break;
            case 'permission':
                $this->initPermission();
                break;
            default:
                $this->warn('不存在当前类型的初始化');
                break;
        }
    }

    protected function getArguments()
    {
        return [
            ['type', InputArgument::REQUIRED, '初始化类型'],
        ];
    }

    /**
     * 初始化菜单数据.
     */
    private function initMenus()
    {
        $this->line('开始初始化菜单数据。。。', 'info');
        $menus = [];
        foreach ($this->menuPath as $relatePath) {
            $aliasPath = BASE_PATH . '/' . $relatePath;
            foreach (glob($aliasPath) as $path) {
                $menus = ArrayHelper::merge($menus, require $path);
            }
        }
        DB::transaction(function () use ($menus) {
            Db::table(Menu::tableName())->truncate();
            Db::table(Menu::tableName())->insert($menus);
        });
        $this->line('菜单初始化完成。', 'info');
    }

    /**
     * 初始化权限数据.
     */
    private function initPermission()
    {
        $this->line('开始初始化权限数据。。。', 'info');
        $permissions = [];
        $routes = [];
        foreach ($this->permissionPath as $relatePath) {
            $aliasPath = BASE_PATH . '/' . $relatePath;
            foreach (glob($aliasPath) as $path) {
                $data = require $path;
                foreach ($data as &$datum) {
                    $routeItems = isset($datum['routes']) ? $datum['routes'] : [];
                    $datum['system'] = isset($datum['system']) ? $datum['system'] : 1;
                    $datum['home_page'] = isset($datum['home_page']) ? $datum['home_page'] : 0;
                    $routeItems = array_map(function ($item) use ($datum) {
                        $item['permission_id'] = $datum['id'];
                        $item['system'] = isset($item['system']) ? $item['system'] : 1;
                        $item['home_route'] = isset($item['home_route']) ? $item['home_route'] : 0;
                        return $item;
                    }, $routeItems);
                    unset($datum['routes']);
                    $routes = ArrayHelper::merge($routes, $routeItems);
                }
                $permissions = ArrayHelper::merge($permissions, $data);
                $this->line("######加载文件【{$path}】完成。", 'info');
            }
        }
        DB::transaction(function () use ($permissions, $routes) {
            Db::table(RbacPermission::tableName())->truncate();
            Db::table(RbacPermission::tableName())->insert($permissions);
            RbacPermission::initPermissions();
            Db::table(RbacPermissionRoute::tableName())->truncate();
            Db::table(RbacPermissionRoute::tableName())->insert($routes);
        });
        $this->line('权限初始化完成。', 'info');
    }
}
