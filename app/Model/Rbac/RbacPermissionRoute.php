<?php

declare (strict_types=1);
namespace App\Model\Rbac;

use App\Model\Model;
/**
 * @property int $id 
 * @property string $route 路由
 * @property int $permission_id 权限ID
 * @property int $system 是否系统，1是
 * @property int $home_route 是否主页路由，1是
 */
class RbacPermissionRoute extends Model
{
    public $operators = false;

    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rbac_permission_routes';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['route', 'permission_id', 'system', 'home_route'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'permission_id' => 'integer', 'system' => 'integer', 'home_route' => 'integer'];

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['route', 'permission_id'], 'required'],
            [['permission_id'], 'integer'],
            [['route'], 'string', 'max' => 255],
            [['route'], 'unique_c'],
//            [['permission_id'], 'exist', 'skipOnError' => true, 'targetClass' => RbacPermission::className(), 'targetAttribute' => ['permission_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'route' => '权限路由',
            'permission_id' => '权限ID',
        ];
    }

    /**
     * 根据路由查找权限路由
     * @param $route
     * @return RbacPermissionRoute|static
     */
    public static function findOrCreateByRoute($route)
    {
        $permissionRoute = self::findOne(['route' => $route]);
        if (! $permissionRoute) {
            $permissionRoute = new RbacPermissionRoute();
            $permissionRoute->route = $route;
        }
        return $permissionRoute;
    }
}