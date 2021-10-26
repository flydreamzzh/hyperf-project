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
namespace App\Core\Traits;

use Hyperf\Utils\Context;

/**
 * 通过make调用注解，生成对象
 * Class HyStaticInstance.
 */
trait HyStaticInstance
{
    protected $instanceKey;

    /**
     * @param array $params
     * @param bool $refresh
     * @return null|mixed|static
     */
    public static function instance($params = [], $refresh = false)
    {
        $key = get_called_class();
        $instance = null;
        if (Context::has($key)) {
            $instance = Context::get($key);
        }

        if ($refresh || is_null($instance) || ! $instance instanceof static) {
            $instance = make(static::class, ...$params);
            Context::set($key, $instance);
        }

        return $instance;
    }
}
