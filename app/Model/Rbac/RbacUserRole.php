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
namespace App\Model\Rbac;

use App\Core\Helpers\ArrayHelper;
use App\Model\Model;
use App\Model\User;
use Hyperf\DbConnection\Db;

/**
 * @property int $id
 * @property int $user_id 用户ID
 * @property int $role_id 角色ID
 * @property string $created_by 创建人
 * @property \Carbon\Carbon $created_at 创建时间
 * @property string $updated_by 修改人
 * @property \Carbon\Carbon $updated_at 更新时间
 */
class RbacUserRole extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rbac_user_roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'role_id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'role_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'role_id'], 'required'],
            [['user_id', 'role_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by'], 'string', 'max' => 64],
            [['user_id', 'role_id'], 'unique_c:user_id&role_id'],
            //            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            //            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => RbacRole::className(), 'targetAttribute' => ['role_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * 判断该用户是否超级管理员.
     * @param $user_id
     * @return bool
     */
    public static function isMaster($user_id)
    {
        $role = RbacRole::find()
            ->alias('roles')
            ->where(['roles.master' => 1, 'user_roles.user_id' => $user_id])
            ->leftJoin(['user_roles' => RbacUserRole::tableName()], 'user_roles.role_id', '=', 'roles.id')
            ->first();
        if ($role) {
            return true;
        }
        return false;
    }

    /**
     * 获取某用户的角色【主键】组.
     * @param string $user_id
     * @return array
     */
    public static function getRoleIdsByUser($user_id)
    {
        $roles = RbacUserRole::find()->where(['user_id' => $user_id])->get()->toArray();
        return ArrayHelper::getColumn($roles, 'role_id');
    }

    /**
     * 获取某用户的角色【对象】组.
     * @param string $user_id
     * @return RbacRole[]
     */
    public static function getRolesByUser($user_id)
    {
        /** @var RbacRole[] $roles */
        return RbacRole::find()
            ->alias('roles')
            ->where(['user_roles.user_id' => $user_id])
            ->leftJoin(['user_roles' => RbacUserRole::tableName()], 'user_roles.role_id', '=', 'roles.id')
            ->distinct()
            ->get();
    }

    /**
     * 获取某角色的所有用户ID.
     * @param string $role_id
     * @return array
     */
    public static function getUserIdsByRole($role_id)
    {
        $users = RbacUserRole::find()->where(['role_id' => $role_id])->get()->toArray();
        return ArrayHelper::getColumn($users, 'user_id');
    }

    /**
     * 获取某角色的所有用户.
     * @param string $role_id
     * @return \Hyperf\Utils\Collection
     */
    public static function getUsersByRole($role_id)
    {
        return User::find()
            ->alias('user')
            ->where(['user_roles.role_id' => $role_id])
            ->leftJoin(['user_roles' => RbacUserRole::tableName()], 'user_roles.user_id', '=', 'user.id')
            ->distinct()
            ->get();
    }

    /**
     * 获取没有授权的用户，前50行数据.
     * @param $role_id
     * @param string $name
     * @return array
     */
    public static function getNoAuthUsers($role_id, $name = '')
    {
        $administrator_id = config('user.administrator_id') ?? 1;
        $users = User::find()->alias('user')->select(['user.id', 'label' => 'user.nickname'])
            ->where(['!=', 'user.id', $administrator_id])
            ->andFilterWhere(['like', 'nickname', $name])
            ->whereExists(function ($query) use ($role_id) {
                $query = RbacUserRole::find()->where('user.id = user_roles.user_id')->where(['role_id' => $role_id]);
            })
            ->limit(50)
            ->distinct()
            ->get()->toArray();
        return $users;
    }

    /**
     * 获取某用户的权限【主键】组.
     * @param $user_id string
     * @return array
     */
    public static function getPermissionIdsByUser($user_id)
    {
        $role_ids = self::getRoleIdsByUser($user_id);
        return RbacRolePermission::getPermissionIdsByRole($role_ids);
    }

    /**
     * 获取某用户的权限【对象】组.
     * @param $user_id string
     * @return RbacPermission[]
     */
    public static function getPermissionsByUser($user_id)
    {
        $role_ids = self::getRoleIdsByUser($user_id);
        return RbacRolePermission::getPermissionsByRole($role_ids);
    }

    /**
     *  获取某用户 是否 拥有某权限.
     * @param array|string $user_id
     * @param string $permissionId
     * @return bool
     */
    public static function isHasPermissionForUser($user_id, $permissionId)
    {
        $role_ids = self::getRoleIdsByUser($user_id);
        return RbacRolePermission::isHasPermissionForRole($role_ids, $permissionId);
    }

    /**
     * 为某用户设置 角色 或 角色组.
     * @param string $user_id
     * @param array|string $roleIds
     * @return bool
     */
    public static function setRoles($user_id, $roleIds)
    {
        $roleIds = is_array($roleIds) ? $roleIds : [$roleIds];
        $oldRoles = self::find()->where(['user_id' => $user_id])->get()->toArray();
        $oldIds = $oldRoles ? ArrayHelper::getColumn($oldRoles, 'role_id') : [];
        $delete_ids = array_diff($oldIds, $roleIds);
        $create_ids = array_diff($roleIds, $oldIds);

        DB::beginTransaction();
        try {
            $delete_flag = true;
            if ($delete_ids) {
                $delete_flag = self::deleteAll(['user_id' => $user_id, 'role_id' => $delete_ids]) ? true : false; //清除不再拥有的权限
            }
            if ($delete_flag && $create_ids) {
                $data = [];
                foreach ($create_ids as $id) {
                    $data[] = array_combine(['user_id', 'role_id'], [$user_id, $id]);
                }
                $bool = Db::table(RbacUserRole::tableName())->insert($data); //添加新的权限
                if (! $bool) {
                    Db::rollBack();
                    return false;
                }
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollBack();
            return false;
        }
    }

    /**
     * 角色分配到用户.
     * @param $role_id
     * @return bool
     */
    public static function setRoleUsers($role_id, array $userIds)
    {
        if ($userIds) {
            $data = [];
            foreach ($userIds as $user) {
                $data[] = array_combine(['role_id', 'user_id'], [$role_id, $user]);
            }
            return Db::table(RbacUserRole::tableName())->insert($data);
        }
        return true;
    }

    /**
     * 移除授予某角色的用户.
     * @param string $role_id
     * @return bool|int
     */
    public static function removeRoleUsers($role_id, array $userIds)
    {
        if ($userIds) {
            return self::deleteAll(['role_id' => $role_id, 'user_id' => $userIds]);
        }
        return true;
    }

    /**
     * 初始化超级管理员.
     * @throws \Exception
     */
    public static function initAdministrator()
    {
        $administrator_id = config('user.administrator_id') ?? 1;
        $userRole = RbacUserRole::find()->alias('user_roles')->where(['user_roles.user_id' => $administrator_id, 'roles.master' => 1])
            ->leftJoin(['roles' => RbacRole::tableName()], 'roles.id', '=', 'user_roles.role_id')->first();
        if (! $userRole) {
            $user = User::findOne(['id' => $administrator_id]);
            if (! $user) {
                $user = new User();
                $user->id = 1;
                $user->username = 'admin';
                $user->setPassword('admin');
                $user->nickname = '超级管理员';
                $user->status = User::STATUS_ACTIVE;
                $user->email = 'administrator@163.com';
                $user->save();
            }
            if ($user->exists()) {
                /** @var RbacUserRole $master */
                $master = RbacRole::find()->where(['master' => 1])->orderBy('id')->first();
                if (! $master) {
                    RbacRole::initRoles();
                    $master = RbacRole::find()->where(['master' => 1])->orderBy('id')->first();
                }
                $user_role = new RbacUserRole();
                $user_role->user_id = $administrator_id;
                $user_role->role_id = $master->id;
                $user_role->save();
            }
        }
    }
}
