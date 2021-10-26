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

use Hyperf\Utils\Collection as BaseCollection;

trait OtherHasAttributes
{
    /**
     * @param $value
     * @return mixed
     */
    public function jsonOrString($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        $data = json_decode($value, true);
        if ($data && (is_object($data)) || (is_array($data) && ! empty($data))) {
            return $data;
        }
        return $value;
    }

    /**
     * @param $key
     * @param $value
     * @return BaseCollection|bool|\Carbon\CarbonInterface|float|int|mixed|string
     */
    protected function castAttribute($key, $value)
    {
        $type = $this->getCastType($key);
        switch ($type) {
            case '!json':
                return $this->jsonOrString($value);
            case strpos($type, 'method:') === 0:
                $method = str_replace('method:', '', $type);
                if (method_exists($this, $method)) {
                    return call_user_func_array([$this, $method], [$key, $value]);
                }
                    return parent::castAttribute($key, $value);
            default:
                return parent::castAttribute($key, $value);
        }
    }
}
