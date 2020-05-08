<?php


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
     * 验证规则名称
     * @return string
     */
    abstract public static function ruleName(): string;

    /**
     * @return ValidateModelInterface|mixed|null
     */
    public function getTargetClass()
    {
        return $this->targetClass = Context::get(ValidateModelInterface::class);
    }

    /**
     * 默认返回的验证规则中文提示
     * @return string
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
     * @return bool
     * @throws \Exception
     */
    public function checkTargetClass()
    {
        $targetClass = $this->getTargetClass();
        if ($targetClass == null || !$targetClass instanceof Model) {
            throw new \Exception('缺少验证的模型数据');
        }
        return true;
    }
}