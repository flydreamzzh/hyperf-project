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
namespace App\Core\Interfaces;

use Hyperf\Validation\Validator;

/**
 * Interface RuleInterface.
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
     * @param $message
     * @param $attribute
     * @param $rule
     * @param $parameters
     * @param Validator $validator
     * @return string
     */
    public function message($message, $attribute, $rule, $parameters, Validator $validator): string;
}
