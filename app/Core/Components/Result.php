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
namespace App\Core\Components;

use App\Constants\StatusCode;
use Hyperf\Utils\Traits\StaticInstance;

/**
 * 返回结果类
 * Class Result.
 */
class Result
{
    use StaticInstance;

    /**
     * @var int 错误码
     */
    private $_errcode;

    /**
     * @var string 提示信息
     */
    private $_errmsg;

    /**
     * @var array 返回数据
     */
    private $_data;

    /**
     * 设置错误码
     * @param int $errcode 错误码
     * @return $this
     */
    public function setErrcode($errcode): Result
    {
        $this->_errcode = $errcode;
        return $this;
    }

    /**
     * 设置返回成功
     * @return $this
     */
    public function setSuccess(): Result
    {
        $this->_errcode = StatusCode::SUCCESS;
        return $this;
    }

    /**
     * 设置提示信息.
     * @param int|string $errmsg 错误码
     * @return $this
     */
    public function setErrmsg($errmsg): Result
    {
        $this->_errmsg = $errmsg;
        return $this;
    }

    /**
     * 设置返回数据.
     * @param array $data 返回数据
     * @return $this
     */
    public function setData(array $data): Result
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * 获取错误码
     */
    public function getErrcode(): int
    {
        return $this->_errcode;
    }

    /**
     * 获取提示信息.
     */
    public function getErrmsg(): string
    {
        return $this->_errmsg;
    }

    /**
     * 获取返回数据.
     */
    public function getData(): array
    {
        return $this->_data;
    }

    /**
     * 是否返回正确.
     */
    public function getIsReturnTrue(): bool
    {
        return $this->_errcode === StatusCode::SUCCESS;
    }

    /**
     * @param array|string $key
     * @param null $value
     */
    public function addData($key, $value = null)
    {
        if (is_array($key)) {
            $this->_data = array_merge($this->_data, $key);
        } elseif ($value !== null) {
            $this->_data[$key] = $value;
        }
    }
}
