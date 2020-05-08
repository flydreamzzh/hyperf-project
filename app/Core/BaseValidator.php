<?php

namespace App\Core;

use App\Core\Helpers\ArrayHelper;
use App\Core\Interfaces\RuleInterface;
use App\Core\Interfaces\ValidateModelInterface;
use App\Core\Validator\Base\BaseRule;
use App\Core\Validator\MethodRule;
use App\Core\Validator\MobileRule;
use App\Core\Validator\NumberRule;
use App\Core\Validator\UniqueRule;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

/**
 * 扩展自定义验证规则
 * Class BaseValidator
 * @package App\Core\Validator
 */
class BaseValidator
{
    protected static $extends = [];

    protected static $defaultMessages = [];

    /**
     * 扩展的验证规则
     * @return array
     */
    protected static function rules()
    {
        return [
            MobileRule::class,
            UniqueRule::class,
            MethodRule::class,
            NumberRule::class,
        ];
    }

    /**
     * 默认验证规则对应的中文提示
     * @return array
     */
    public static function messages(): array
    {
        return [
            'mobile' => '手机号不正确'
        ];
    }

    public static function getValidator(): ValidatorFactoryInterface
    {
        static $validator = null;
        if (is_null($validator)) {
            $container = ApplicationContext::getContainer();
            $validator = $container->get(ValidatorFactoryInterface::class);
            self::initExtends();
            self::registerExtends($validator, self::$extends);
        }

        return $validator;
    }

    protected static function initExtends()
    {
        $rules = self::rules();
        foreach ($rules as $rule) {
            /** @var BaseRule $rule */
            self::$extends[$rule::ruleName()] = new $rule;
            $rule::defaultMessage() && self::$defaultMessages[$rule::ruleName()] = $rule::defaultMessage();
        }
    }

    protected static function registerExtends(ValidatorFactoryInterface $validator, array $extends)
    {
        foreach ($extends as $key => $extend) {
            if ($extend instanceof RuleInterface) {
                $validator->extend($key, function (...$args) use ($extend) {
                    return call_user_func_array([$extend, RuleInterface::PASSES_NAME], $args);
                });
                $validator->replacer($key, function (...$args) use ($extend) {
                    return call_user_func_array([$extend, RuleInterface::MESSAGE_NAME], $args);
                });
            }
        }
    }

    /**
     * 规则验证生成器
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $attributes
     * @param Model $model
     * @return ValidatorInterface
     */
    public static function make(array $data, array $rules, array $messages = [], array $attributes = [], Model $model = null): ValidatorInterface
    {
        if ($model) {
            Context::set(ValidateModelInterface::class, $model);
        }
        $validator = self::getValidator();
        $messages = ArrayHelper::merge(self::getMessages(), $messages);
        $valid = $validator->make($data, $rules, $messages, $attributes);
        return $valid;
    }

    /**
     * @return array
     */
    private static function getMessages(): array
    {
        return ArrayHelper::merge(static::messages(), static::$defaultMessages);
    }
}