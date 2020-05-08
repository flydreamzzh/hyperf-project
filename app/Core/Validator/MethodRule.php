<?php


namespace App\Core\Validator;


use App\Core\Validator\Base\BaseRule;
use Hyperf\Validation\Validator;

/**
 * 模型函数验证
 * Class MethodRule
 * @package App\Core\Validator
 */
class MethodRule extends BaseRule
{
    /**
     * 验证规则名称
     * @return string
     */
    public static function ruleName(): string
    {
        return 'method';
    }

    /**
     * 默认返回的验证规则中文提示
     * @return string
     */
    public static function defaultMessage(): string
    {
        return ':attribute 不符合规则';
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
        $method = reset($parameters);
        if (empty($method) || !method_exists($targetClass, $method)) {
            throw new \Exception("Method rule 不存在方法{$method}");
        }
        return call_user_func_array([$targetClass, $method], [$attribute, $validator]);
    }
}