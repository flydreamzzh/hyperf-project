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
 * 手机号验证规则
 * Class MobileRule.
 */
class MobileRule extends BaseRule
{
    /**
     * 验证规则名称.
     */
    public static function ruleName(): string
    {
        return 'mobile';
    }

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
