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
use Hyperf\DbConnection\Db;

/**
 * @property int $id
 * @property int $role_id 角色ID
 * @property int $permission_id 权限ID
 * @property string $created_by 创建人
 * @property \Carbon\Carbon $created_at 创建时间
 * @property string $updated_by 修改人
 * @property \Carbon\Carbon $updated_at 更新时间
 */
class RbacRolePermission extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rbac_role_permissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['role_id', 'permission_id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'role_id' => 'integer', 'permission_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['role_id', 'permission_id'], 'required'],
            [['role_id', 'permission_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by'], 'string', 'max' => 64],
            [['role_id', 'permission_id'], 'unique_c:,role_id&permission_id'],
            //            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => RbacRole::className(), 'targetAttribute' => ['role_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'role_id' => 'Role ID',
            'permission_id' => 'Permission ID',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * 获取某角色或角色组的权限【主键】组.
     * @param array|string $role_id
     * @return array
     */
    public static function getPermissionIdsByRole($role_id)
    {
        $role_permissions = self::find()->where(['role_id' => $role_id])->get()->toArray();
        return ArrayHelper::getColumn($role_permissions, 'permission_id');
    }

    /**
     * 获取某角色或某角色组的权限【对象】组.
     * @param array|string $role_id
     * @return RbacPermission[]
     */
    public static function getPermissionsByRole($role_id)
    {
        $permissionIds = self::getPermissionIdsByRole($role_id);
        /** @var RbacPermission[] $permissions */
        return RbacPermission::find()->where(['id' => $permissionIds])->get();
    }

    /**
     *  获取某角色或角色组 是否 拥有某权限.
     * @param array|string $role_id
     * @param string $permissionId
     * @return bool
     */
    public static function isHasPermissionForRole($role_id, $permissionId)
    {
        if ($role_id && self::findOne(['role_id' => $role_id, 'permission_id' => $permissionId])) {
            return true;
        }
        return false;
    }

    /**
     * 为角色授予权限.
     * @param $role_id string
     * @param $permissionIds array 权限ID组
     * @return bool
     */
    public static function setPermissions($role_id, array $permissionIds)
    {
        $oldPermissions = self::find()->where(['role_id' => $role_id])->get()->toArray();
        $oldIds = $oldPermissions ? ArrayHelper::getColumn($oldPermissions, 'permission_id') : [];
        $delete_ids = array_diff($oldIds, $permissionIds);
        $create_ids = array_diff($permissionIds, $oldIds);

        Db::beginTransaction();
        try {
            $delete_flag = true;
            if ($delete_ids) {
                $delete_flag = self::deleteAll(['role_id' => $role_id, 'permission_id' => $delete_ids]) ? true : false; //清除不再拥有的权限
            }
            if ($delete_flag && $create_ids) {
                $data = [];
                foreach ($create_ids as $id) {
                    $data[] = array_combine(['role_id', 'permission_id'], [$role_id, $id]);
                }
                $bool = Db::table(RbacPermission::tableName())->insert($data);
                if (! $bool) {
                    Db::rollBack();
                    return false;
                }
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollBack();
            return false;
        }
    }
}
