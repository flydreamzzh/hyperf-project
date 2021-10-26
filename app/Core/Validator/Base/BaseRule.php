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
namespace App\Core\Validator\Base;

use App\Core\Interfaces\RuleInterface;
use App\Core\Interfaces\ValidateModelInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Utils\Context;
use Hyperf\Validation\Validator;

abstract class BaseRule implements RuleInterface
{
    /**
     * @var Model
     */
    public $targetClass;

    /**
     * 验证规则名称.
     */
    abstract public static function ruleName(): string;

    /**
     * @return null|mixed|ValidateModelInterface
     */
    public function getTargetClass(): ?ValidateModelInterface
    {
        return $this->targetClass = Context::get(ValidateModelInterface::class);
    }

    /**
     * 默认返回的验证规则中文提示.
     */
    abstract public static function defaultMessage(): string;

    /**
     * @param $message
     * @param $attribute
     * @param $rule
     * @param $parameters
     * @param Validator $validator
     * @return string
     */
    public function message($message, $attribute, $rule, $parameters, Validator $validator): string
    {
        return $message;
    }

    /**
     * @throws \Exception
     * @return bool
     */
    public function checkTargetClass(): bool
    {
        $targetClass = $this->getTargetClass();
        if ($targetClass == null || ! $targetClass instanceof Model) {
            throw new \Exception('缺少验证的模型数据');
        }
        return true;
    }
}
