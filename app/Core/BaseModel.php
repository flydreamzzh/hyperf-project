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
namespace App\Core;

use App\Core\Components\Builder as Builder;
use App\Core\Interfaces\ValidateModelInterface;
use App\Core\Traits\HasOperators;
use App\Core\Traits\IdeHelperTrait;
use App\Core\Traits\OtherHasAttributes;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Database\Model\Collection;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;

/**
 * 模型基础类
 * Class BaseModel.
 */
class BaseModel extends Model
{
    use IdeHelperTrait;
    use HasOperators;
    use OtherHasAttributes;

    /**
     * The name of the "created by" column.
     *
     * @var string
     */
    const CREATED_BY = 'created_by';

    /**
     * The name of the "updated by" column.
     *
     * @var string
     */
    const UPDATED_BY = 'updated_by';

    /**
     * @var array
     */
    private $_errors = [];

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return (new static())->getTable();
    }

    /**
     * 字段对应其中文翻译zh_CN.
     * @return array
     */
    public function attributeLabels(): array
    {
        return [];
    }

    /**
     * 验证场景.
     */
    public function scenarios(): array
    {
        return [];
    }

    /**
     * 验证模型数据.
     * @return bool
     */
    public function validate(): bool
    {
        Context::set(ValidateModelInterface::class, $this);
        $this->clearErrors();
        [$rules, $messages] = $this->getRules();
        $container = ApplicationContext::getContainer();
        $translator = $container->get(TranslatorInterface::class);
        $attributes = $translator->getLocale() == 'zh_CN' ? $this->attributeLabels() : [];
        $validator = BaseValidator::make($this->getAttributes(), $rules, $messages, $attributes);
        if ($validator->fails()) {
            $this->_errors = $validator->errors()->all();
            return false;
        }
        return true;
    }

    /**
     * @param string $attribute
     * @param mixed $message
     * @return string
     */
    public function addError(string $attribute, $message): string
    {
        return $this->_errors[$attribute] = $message;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->_errors;
    }

    /**
     * 获取第一个错误信息.
     * @return mixed
     */
    public function getFirstError()
    {
        $error = current($this->_errors);
        return is_array($error) ? current($error) : $error;
    }

    /**
     * 清除错误信息.
     * @param null $attribute
     */
    public function clearErrors($attribute = null)
    {
        if ($attribute === null) {
            $this->_errors = [];
        } else {
            unset($this->_errors[$attribute]);
        }
    }

    /**
     * @return string
     */
    public static function primaryKey()
    {
        return (new static())->getKeyName();
    }

    /**
     * 获取主键.
     * @return array|string
     */
    public function getPrimaryKey()
    {
        $keys = $this->primaryKey;
        if (! is_array($keys)) {
            return $this->getAttribute($keys) ?? null;
        }
        $values = [];
        foreach ($keys as $name) {
            $values[$name] = $this->getAttribute($name) ?? null;
        }
        return $values;
    }

    /**
     * 获取表前缀
     * @return array|bool|false|mixed|string|void
     */
    public static function getTablePrefix()
    {
        return env('DB_PREFIX') ?? '';
    }

    /**
     * 获取完整的表名.
     * @param null|string $table
     * @return string
     */
    public static function getAbsoluteTableName($table = null): string
    {
        if ($table) {
            return self::getTablePrefix() . $table;
        }
        return self::getTablePrefix() . self::tableName();
    }

    /**
     * 验证规则再保存数据.
     * @param array $options
     * @return bool
     */
    public function save(array $options = []): bool
    {
        if (! $this->validate()) {
            return false;
        }
        if (! $this->beforeSave()) {
            return false;
        }
        if ($bool = parent::save($options)) {
            $this->afterSave();
        }
        return $bool;
    }

    /**
     * 保存数据且验证数据.
     * @param array $options
     * @param bool $validate
     * @return bool
     */
    public function saveAndValidate(array $options = [], $validate = true): bool
    {
        if ($validate && ! $this->validate()) {
            return false;
        }
        return parent::save($options);
    }

    /**
     * 保存数据且验证数据.
     * @param array $options
     * @return bool
     */
    public function saveNoValidate(array $options = []): bool
    {
        return parent::save($options);
    }

    /**
     * Convert the model instance to an array.
     * @param array $attributes
     */
    public function toArray($attributes = []): array
    {
        if ($attributes) {
            $arr = parent::toArray();
            $data = [];
            foreach ($attributes as $attribute) {
                $data[$attribute] = $arr[$attribute] ?? null;
            }
            return $data;
        }
        return parent::toArray();
    }

    /**
     * query()别名.
     * @return Builder|\Hyperf\Database\Model\Builder
     */
    public static function find()
    {
        return self::query();
    }

    /**
     * 获取单条数据模型对象
     * @param $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return null|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|static
     */
    public static function findOne($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (count(func_get_args()) == 1) {
            if ($column instanceof BaseModel) {
                return self::query()->where([static::primaryKey() => $column->getPrimaryKey()])->first();
            }
            if (! is_array($column)) {
                return self::query()->where([static::primaryKey() => $column])->first();
            }
        }
        return self::query()->where(...func_get_args())->first();
    }

    /**
     * 获取多条数据模型对象
     * @param $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return Collection|static[]
     */
    public static function findAll($column, $operator = null, $value = null, $boolean = 'and')
    {
        return self::query()->where(...func_get_args())->get();
    }

    /**
     * @param $condition
     * @return bool|int
     */
    public static function deleteAll($condition)
    {
        if (empty($condition)) {
            return false;
        }
        return (new static())->where($condition)->delete();
    }

    /**
     * @param $condition
     * @param $attributes
     * @return bool|int
     */
    public static function updateAll($attributes, $condition)
    {
        if (empty($condition) || empty($attributes)) {
            return false;
        }
        return (new static())->where($condition)->update($attributes);
    }

    /**
     * 验证规则.
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getRules(): array
    {
        $validateRules = [];
        $validateMessages = [];
        $rules = $this->rules();
        foreach ($rules as $rule) {
            [$columns, $validateType] = $rule;
            $message = $rule['message'] ?? null;
            $columns = is_array($columns) ? $columns : [$columns];
            foreach ($columns as $column) {
                if (isset($validateRules[$column])) {
                    $validateRules[$column] .= "|{$validateType}";
                } else {
                    $validateRules[$column] = $validateType;
                }
                if ($message) {
                    if (is_array($message)) {
                        $types = explode('|', $validateType);
                        foreach ($types as $index => $type) {
                            $messageType = preg_replace('/(:).*/', '', $type);
                            isset($message[$index]) && $validateMessages["{$column}.{$messageType}"] = (string) $message[$index];
                        }
                    } else {
                        $messageType = preg_replace('/(:).*/', '', $validateType);
                        $validateMessages["{$column}.{$messageType}"] = (string) $message;
                    }
                }
            }
        }
        return [$validateRules, $validateMessages];
    }

    /**
     * 保存前操作.
     * @return bool
     */
    protected function beforeSave(): bool
    {
        return true;
    }

    /**
     * 保存后操作.
     */
    protected function afterSave()
    {
    }

    /**
     * 记录操作者.
     * @param \Hyperf\Database\Model\Builder $query
     * @return bool
     */
    protected function performInsert(\Hyperf\Database\Model\Builder $query)
    {
        if ($this->usesOperators()) {
            $this->updateOperators();
        }
        return parent::performInsert($query); // TODO: Change the autogenerated stub
    }

    /**
     * 记录操作者.
     * @param \Hyperf\Database\Model\Builder $query
     * @return bool
     */
    protected function performUpdate(\Hyperf\Database\Model\Builder $query): bool
    {
        if ($this->usesOperators()) {
            $this->updateOperators();
        }
        return parent::performUpdate($query); // TODO: Change the autogenerated stub
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return Builder
     */
    protected function newBaseQueryBuilder(): Builder
    {
        $connection = $this->getConnection();

        return new Builder($connection, $connection->getQueryGrammar(), $connection->getPostProcessor());
    }
}
