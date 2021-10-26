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
use App\Core\Helpers\ArrayHelper;
use Hyperf\DbConnection\Db;

/**
 * This is the model class for table "sys_config".
 *
 * @property string $name 名称
 * @property string $label 标题
 * @property string $tip 提示
 * @property string $type 类型
 * @property float $maxSize 文件大小
 * @property int $multiple 是否多选，1是
 * @property int $required 是否必填，1是
 * @property int $system 是否系统配置，1是
 * @property int $sort 排序
 * @property string $options 选项
 * @property string $group
 */
class ConfigModel extends BaseModel
{
    public $operators = false;

    public $timestamps = false;

    /**
     * todo 分组.
     * @var array 配置分组
     */
    public static $groups = [
        'basic',
    ];

    /**
     * 表单类型.
     * @var array
     */
    public static $types = [
        'text' => '单行文本',
        'textarea' => '多行文本',
        'number' => '数字',
        'switch' => '开关',
        'select' => '下拉框',
        'radio' => '单选框',
        'checkbox' => '复选框',
        'time' => '时间',
        'timerange' => '时间段',
        'date' => '日期',
        'daterange' => '日期段',
        'datetime' => '日期',
        'datetimerange' => '日期时间段',
        'image' => '图片',
        'file' => '文件',
    ];

    protected $primaryKey = 'name';

    protected $keyType = 'varchar';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'label', 'tip', 'type', 'maxSize', 'multiple', 'required', 'system', 'sort', 'options', 'group'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'maxSize' => 'float',
        'multiple' => 'integer',
        'required' => 'integer',
        'system' => 'integer',
        'sort' => 'integer',
        'options' => '!json',
    ];

    /**
     * todo 键名映射.
     * @var array
     */
    protected static $keyMap = [];

    /**
     * 上传文件的位置.
     * @var string
     */
    protected $dir = '/uploads/config';

    /**
     * 保存值时需要的分组名称，每个sqlite文件为一个分组.
     * @var string
     */
    protected static $value_group = null;

    /**
     * @var string
     */
    protected $value;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name', 'label'], 'required'],
            [['name', 'label', 'tip', 'type', 'group'], 'string'],
            [['maxSize'], 'number'],
            [['multiple', 'required', 'system', 'sort'], 'integer'],
            [['label', 'group'], 'unique_c:label&group'],
            [['name'], 'unique_c'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => '配置名称',
            'label' => '配置标题',
            'tip' => '简要描述',
            'type' => '类型',
            'maxSize' => '文件大小', //单位M
            'multiple' => '是否多选', //1是
            'required' => '是否必填', //1是
            'system' => '是否系统配置', //1是
            'sort' => '排序',
            'options' => '选项',
            'group' => '分组',
        ];
    }

    /**
     * 获取某配置项对象，否则创建.
     * @param $name
     * @return null|ConfigModel
     */
    public static function findOrCreate($name): ?ConfigModel
    {
        if (! $item = self::findOne(['name' => $name])) {
            $item = new self();
            $item->name = $name;
        }
        return $item;
    }

    /**
     * 获取某配置项的值
     * @param string $name
     * @return mixed
     */
    public static function getOneConfig(string $name)
    {
        $item = self::findOne(['name' => $name]);
        return $item->getFormatValue();
    }

    /**
     * 获取配置参数及其值
     * @param $names array
     * @return array
     */
    public static function getConfigs($names = []): array
    {
        $configs = [];
        if ($names) {
            $items = self::find()->where(['name' => $names])->get();
        } else {
            $items = self::find()->get();
        }
        foreach ($items as $key => $item) {
            /** @var $item $this */
            $value = $item->getFormatValue();
            $key = ! empty(self::$keyMap[$item['name']]) ? self::$keyMap[$item['name']] : $item['name'];
            $configs[$key] = $value;
        }
        return $configs;
    }

    /**
     * 获取类型中文名称.
     * @param string $type
     * @return mixed|string
     */
    public static function getType(string $type): string
    {
        if (isset(self::$types[$type])) {
            return self::$types[$type];
        }
        return '类型异常';
    }

    /**
     * @param $name
     * @return null|static
     */

    /**
     * @param $name
     * @return null|\App\Core\Components\Builder|object
     */
    public static function getConfigByName($name)
    {
        return self::find()->where(['name' => $name])->first();
    }

    /**
     * 获取数据.
     * @return mixed
     */
    public function getFormatValue()
    {
        $value = ConfigValue::getConfigValue($this->name, static::$value_group);
        if (in_array($this->type, ['checkbox', 'timerange', 'daterange', 'datetimerange']) || ($this->type == 'select' && $this->multiple)) {
            $data = json_decode($value);
            if ($data && (is_object($data)) || (is_array($data) && ! empty($data))) {
                return $data;
            }
        } elseif (in_array($this->type, ['image', 'file'])) {
            if ($value) {
                $path = BASE_PATH . '/web' . $this->dir;
                if (file_exists($path . '/' . $value)) {
                    $value = $this->dir . '/' . $value;
                } else {
                    $value = '';
                }
            }
        }

        return $value;
    }

    /**
     * 设置数据.
     * @param string $value
     */
    public function setFormatValue(string $value)
    {
        $this->value = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * 获取表单配置项.
     * @param string $group
     * @return array
     */
    public static function getFormItems($group = null): array
    {
        $value = [];
        $items = self::find()->andFilterWhere(['group' => $group])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->get();
        foreach ($items as $key => $item) {
            /* @var $item $this */
            $items[$key]['value'] = $item->getFormatValue();
            $value[$item['name']] = $item['value'];
            $items[$key] = $item->toArray();
        }
        return [
            'form' => $value,
            'items' => $items,
        ];
    }

    /**
     * 保存配置.
     * @param $params [key => value]
     * @return array
     */
    public static function saveConfig($params): array
    {
        if (! static::$value_group) {
            return ['success' => false, 'message' => '未设置配置项分组'];
        }
        $params = ArrayHelper::merge($params, $_FILES);
        Db::beginTransaction();
        try {
            $deleteFiles = [];
            foreach ($params as $name => $value) {
                if ($item = self::getConfigByName($name)) {
                    $result = $item->saveForType($value, false);
                    if (! $result['success']) {
                        Db::rollBack();
                        return ['success' => false, 'message' => $result['message']];
                    }
                    $deleteFiles[] = $result['unlink'];
                }
            }
            foreach ($deleteFiles as $deleteFile) {
                @unlink($deleteFile);
            }
            Db::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            Db::rollBack();
            return ['success' => false, 'message' => '保存数据异常'];
        }
    }

    /**
     * 保存不同类型配置项数据.
     * @param $value
     * @param bool $deleteOldFile
     * @return array 成功与否，要删除的文件
     */
    public function saveForType($value, $deleteOldFile = true): array
    {
        $unlinkFile = null;
        if (! in_array($this->type, ['image', 'file'])) {
            $this->setFormatValue($value);
        } else {
            $this->value = ConfigValue::getConfigValue($this->name, static::$value_group);
            if (! $value && $this->value) {
                $unlinkFile = BASE_PATH . '/web' . $this->dir . '/' . $this->value;
                $this->value = '';
            } else {
                if (isset($_FILES[$this->name])) {
                    $oldFile = BASE_PATH . '/web' . $this->dir . '/' . $this->value;
                    $result = $this->saveFileByUpload($_FILES[$this->name], $this->dir, $this->maxSize);
                    if (! $result['success']) {
                        return ['success' => false, 'message' => $result['message']];
                    }
                    $unlinkFile = $oldFile;
                }
            }
        }
        if ($deleteOldFile) {
            @unlink($unlinkFile);
        }
        if (ConfigValue::setConfigValue($this->name, static::$value_group, $this->value)) {
            return ['success' => true, 'unlink' => $unlinkFile];
        }
        return ['success' => false, 'message' => '保存数据失败'];
    }

    /**
     * 保存上传的文件.
     * @param array $FILES php原上传文件格式
     * @param string $dir 上传的文件夹
     * @param bool|int $maxSize 最大值 KB
     * @return array
     */
    public function saveFileByUpload($FILES, $dir, $maxSize = false): array
    {
        if ($maxSize && $FILES['size'] > $maxSize * 1024 * 1024) {//文件大小限制
            return ['success' => false, 'message' => "上传文件大小不能大于{$maxSize}M"];
        }
        $path = BASE_PATH . '/web' . $dir;

        if ($FILES['size'] == 0) {//文件不存在
            return ['success' => false, 'message' => '上传的文件不存在'];
        }
        $name = preg_replace('/[\x{4e00}-\x{9fa5}]/u', '*', $FILES['name']);
        $name = '[' . rand(1000, 9999) . date('YmdHis') . ']' . $name;
        $this->value = $name;

        $url = $path . '/' . $name;
        if (! move_uploaded_file($FILES['tmp_name'], $url)) {
            return ['success' => false, 'message' => '上传文件失败'];
        }
        return ['success' => true, 'message' => '上传文件成功'];
    }

    /**
     * @return bool
     */
    protected function beforeSave(): bool
    {
        $this->options = is_array($this->options) ? json_encode($this->options, true) : $this->options;
        return parent::beforeSave(); // TODO: Change the autogenerated stub
    }
}
