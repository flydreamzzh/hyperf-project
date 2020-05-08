<?php


namespace App\Core\Validator;


use App\Core\Helpers\StringHelper;
use App\Core\Validator\Base\BaseRule;
use Hyperf\Validation\Validator;

/**
 * Class NumberRule
 * @package App\Core\Validator
 */
class NumberRule extends BaseRule
{
    /**
     * @var bool whether the attribute value can only be an integer. Defaults to false.
     */
    public $integerOnly = false;
    /**
     * @var string the regular expression for matching integers.
     */
    public $integerPattern = '/^\s*[+-]?\d+\s*$/';
    /**
     * @var string the regular expression for matching numbers. It defaults to a pattern
     * that matches floating numbers with optional exponential part (e.g. -1.23e-10).
     */
    public $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';

    /**
     * 验证规则名称
     * @return string
     */
    public static function ruleName(): string
    {
        return 'number';
    }

    /**
     * 默认返回的验证规则中文提示
     * @return string
     */
    public static function defaultMessage(): string
    {
        return ':attribute 必须是数字';
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
        if ($this->isNotNumber($value)) {
            return false;
        }
        $pattern = $this->integerOnly ? $this->integerPattern : $this->numberPattern;

        if (!preg_match($pattern, StringHelper::normalizeNumber($value))) {
            return false;
        }
        return true;
    }

    /*
     * @param mixed $value the data value to be checked.
     */
    private function isNotNumber($value)
    {
        return is_array($value)
            || is_bool($value)
            || (is_object($value) && !method_exists($value, '__toString'))
            || (!is_object($value) && !is_scalar($value) && $value !== null);
    }
}