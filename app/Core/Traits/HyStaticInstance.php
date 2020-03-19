<?php


namespace App\Core\Traits;


use Hyperf\Utils\Context;

/**
 * 通过make调用注解，生成对象
 * Class HyStaticInstance
 * @package App\Traits
 */
Trait HyStaticInstance
{
    protected $instanceKey;

    /**
     * @param array $params
     * @param bool $refresh
     * @return HyStaticInstance|mixed|static|null
     */
    public static function instance($params = [], $refresh = false)
    {
        $key = get_called_class();
        $instance = null;
        if (Context::has($key)) {
            $instance = Context::get($key);
        }

        if ($refresh || is_null($instance) || ! $instance instanceof static) {
            $instance = make(self::class, ...$params);
            Context::set($key, $instance);
        }

        return $instance;
    }
}