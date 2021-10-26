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
namespace App\Model\Base;

use App\Core\BaseModel;

/**
 * This is the model class for table "{{%config_value}}".
 *
 * @property int $id
 * @property string $name 键名
 * @property string $value 值
 * @property string $group 分组
 * @property string $created_at 创建时间
 * @property string $created_by 创建人
 * @property string $updated_at 修改时间
 * @property string $updated_by 修改人
 */
class ConfigValue extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'config_value';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'value', 'group'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name', 'group'], 'required'],
            [['name', 'group'], 'max:255'],
            [['created_by', 'updated_by'], 'max:64'],
            [['name', 'group'], 'unique_c:name&group'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => '键名',
            'value' => '值',
            'group' => '分组',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * @param string $name
     * @param string $group
     * @return null|ConfigValue|static
     */
    public static function findOrCreate(string $name, string $group): ?ConfigValue
    {
        if (! $configValue = ConfigValue::findOne(['name' => $name, 'group' => $group])) {
            $configValue = new ConfigValue();
            $configValue->name = $name;
            $configValue->group = $group;
        }
        return $configValue;
    }

    /**
     * 获取某键值
     * @param string $name
     * @param string $group
     * @return string
     */
    public static function getConfigValue(string $name, string $group): string
    {
        $configValue = ConfigValue::findOne(['name' => $name, 'group' => $group]);
        return $configValue ? $configValue->value : '';
    }

    /**
     * 保存值
     * @param string $name
     * @param string $group
     * @param string $value
     * @return bool
     */
    public static function setConfigValue(string $name, string $group, string $value): bool
    {
        $configValue = ConfigValue::findOrCreate($name, $group);
        $configValue->value = $value;
        return $configValue->save();
    }
}
