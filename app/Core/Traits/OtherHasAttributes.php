<?php


namespace App\Core\Traits;


use Hyperf\Utils\Collection as BaseCollection;

trait OtherHasAttributes
{
    /**
     * @param $key
     * @param $value
     * @return bool|\Carbon\CarbonInterface|float|BaseCollection|int|mixed|string
     */
    protected function castAttribute($key, $value)
    {
        $type = $this->getCastType($key);
        switch ($type) {
            case '!json':
                return $this->jsonOrString($value);
            case strpos($type,'method:') === 0:
                $method = str_replace('method:', '', $type);
                if (method_exists($this, $method)) {
                    return call_user_func_array([$this, $method], [$key, $value]);
                } else {
                    return parent::castAttribute($key, $value);
                }
            default:
                return parent::castAttribute($key, $value);
        }
    }

    /**
     * @param $value
     * @return mixed
     */
    public function jsonOrString($value)
    {
        if (is_array($value)) {
            return $value;
        }
        $data = json_decode($value, true);
        if ($data && (is_object($data)) || (is_array($data) && !empty($data))) {
           return $data;
        }
        return $value;
    }

}