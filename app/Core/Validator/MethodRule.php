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
namespace App\Core\Validator;

use App\Core\Validator\Base\BaseRule;
use Hyperf\Validation\Validator;

/**
 * 模型函数验证
 * Class MethodRule.
 */
class MethodRule extends BaseRule
{
    /**
     * 验证规则名称.
     */
    public static function ruleName(): string
    {
        return 'method';
    }

    /**
     * 默认返回的验证规则中文提示.
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
        if (empty($method) || ! method_exists($targetClass, $method)) {
            throw new \Exception("Method rule 不存在方法{$method}");
        }
        return call_user_func_array([$targetClass, $method], [$attribute, $validator]);
    }
}
