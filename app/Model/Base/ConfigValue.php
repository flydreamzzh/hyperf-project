<?php

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
    public function attributeLabels()
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
     * @return ConfigValue|null|static
     */
    public static function findOrCreate($name, $group)
    {
        if (!$configValue = ConfigValue::findOne(['name' => $name, 'group' => $group])) {
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
    public static function getConfigValue($name, $group)
    {
        $configValue = ConfigValue::findOne(['name' => $name, 'group' => $group]);
        $value = $configValue ? $configValue->value : '';
        return $value;
    }

    /**
     * 保存值
     * @param string $name
     * @param string $group
     * @param string $value
     * @return bool
     */
    public static function setConfigValue($name, $group, $value)
    {
        $configValue = ConfigValue::findOrCreate($name, $group);
        $configValue->value = $value;
        return $configValue->save();
    }

}
