<?php


namespace App\Core\Validator;


use App\Core\Validator\Base\BaseRule;
use Hyperf\Database\Model\Model;
use Hyperf\Validation\Validator;

/**
 * 模型数据唯一性验证
 * Class UniqueRule
 * @package App\Core\Validator
 */
class UniqueRule extends BaseRule
{
    /**
     * 验证规则名称
     * @return string
     */
    public static function ruleName(): string
    {
        return 'unique_c';
    }

    /**
     * 默认返回的验证规则中文提示
     * @return string
     */
    public static function defaultMessage(): string
    {
        return ':attribute 已存在';
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param Validator $validator
     * @return bool
     * @throws \Exception
     */
    public function passes($attribute, $value, $parameters, Validator $validator): bool
    {
        $this->checkTargetClass();
        $targetClass = $this->getTargetClass();
        $columns = reset($parameters);
        $columns = !empty($columns) ? explode('&', $columns) : [$attribute];
        return !$this->modelExists($targetClass, $columns);
    }

    /**
     * @param Model $model
     * @param array $attributes
     * @return mixed
     */
    public function modelExists($model, $attributes)
    {
        $condition = [];
        foreach ($attributes as $attribute) {
            if ($model->getAttribute($attribute) !== null) {
                $condition[$attribute] = $model->getAttribute($attribute);
            }
        }
        if (!$condition) {
            return false;
        }

        $models = $model->newQuery()->where($condition)->limit(2)->limit(2)->get()->toArray();
        $n = count($models);
        if ($n === 1) {
            // if there is one record, check if it is the currently validated model
            $dbModel = reset($models);
            $pks = [$model->getKeyName()];
            $pk = [];
            $oldPks = [];
            foreach ($pks as $pkAttribute) {
                $pk[$pkAttribute] = $dbModel[$pkAttribute];
                $oldPks[$pkAttribute] = $model->getOriginal($pkAttribute);
            }
            $exists = ($pk != $oldPks);
        } else {
            // if there is more than one record, the value is not unique
            $exists = $n > 1;
        }
        return $exists;
    }

}