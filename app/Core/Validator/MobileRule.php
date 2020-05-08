<?php


namespace App\Core\Validator;


use App\Core\Validator\Base\BaseRule;
use Hyperf\Validation\Validator;

/**
 * 手机号验证规则
 * Class MobileRule
 * @package App\Core\Validator
 */
class MobileRule extends BaseRule
{
    /**
     * 验证规则名称
     * @return string
     */
    public static function ruleName(): string
    {
        return 'mobile';
    }

    /**
     * @return string
     */
    public static function defaultMessage(): string
    {
        return ':attribute 必须为一个有效的手机号码';
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param Validator $validator
     * @return bool
     */
    public function passes($attribute, $value, $parameters, Validator $validator): bool
    {
        return preg_match('/^1\d{10}$/', $value);
    }
}