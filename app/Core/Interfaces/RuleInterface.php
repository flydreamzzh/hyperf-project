<?php


namespace App\Core\Interfaces;

use Hyperf\Validation\Validator;

/**
 * Interface RuleInterface
 * @package App\Lib\_Validator\Rules
 */
interface RuleInterface
{
    const PASSES_NAME = 'passes';
    const MESSAGE_NAME = 'message';

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param Validator $validator
     * @return bool
     */
    public function passes($attribute, $value, $parameters, Validator $validator): bool;

    /**
     *
     * @param $message
     * @param $attribute
     * @param $rule
     * @param $parameters
     * @param Validator $validator
     * @return string
     */
    public function message($message, $attribute, $rule, $parameters, Validator $validator): string;

}