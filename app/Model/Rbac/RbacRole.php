<?php

declare (strict_types=1);

namespace App\Model\Rbac;

use App\Model\Model;

/**
 * @property int $id
 * @property string $name 角色名
 * @property string $description 描述
 * @property int $master 是否超管，1是
 * @property int $system 是否系统，1是
 * @property string $created_by 创建人
 * @property \Carbon\Carbon $created_at 创建时间
 * @property string $updated_by 修改人
 * @property \Carbon\Carbon $updated_at 更新时间
 */
class RbacRole extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rbac_roles';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'master', 'system'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'master' => 'integer', 'system' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * 额外显示映射的字段名称
     * @var array
     */
    public static $default_mapped = ['label' => 'name'];

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['master', 'system'], 'integer'],
            [['name'], 'between:2,20'],
            [['name'], 'regex:/^[\x{4e00}-\x{9fa5}\w_]+$/u', 'message' => 30003],
            [['description'], 'max:255'],
            [['created_by', 'updated_by'], 'max:64'],
            ['name', 'unique_c', 'message' => 30001],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '角色名称',
            'description' => '角色描述',
            'master' => '是否超管，1是',
            'system' => '是否系统，1是',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * 获取所有角色
     * @param array $other_columns
     * @return array
     */
    public static function getRoles($other_columns = [])
    {
        $columns = array_merge($other_columns, ['roles.name', 'roles.description', 'roles.id', 'roles.system']);
        $roles = self::find()
            ->alias('roles')
            ->select($columns)
            ->orderBy(['master' => SORT_DESC, 'system' => SORT_DESC])
            ->orderByRaw('convert(name using gbk) asc')
            ->get()->toArray();
        return $roles;
    }

    /**
     * 初始化系统角色【超级管理员】
     * @throws \Exception
     */
    public static function initRoles()
    {
        self::deleteAll(['master' => 0, 'name' => '超级管理员']);
        $master = self::find()->where(['master' => 1])->orderBy(['id' => SORT_ASC])->first();
        if (!$master) {
            $master = new RbacRole();
        }
        $master->name = '超级管理员';
        $master->master = 1;
        $master->system = 1;
        $master->description = "该角色的用户可以对系统作任何操作。";
        if (!$master->save()) {
            return false;
        }
    }
}