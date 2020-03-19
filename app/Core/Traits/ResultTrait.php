<?php


namespace App\Core\Traits;

use App\Core\Components\Result;

Trait ResultTrait
{
    /**
     * 成功返回
     * @param string $errMsg 提示信息
     * @param array $data 响应数据
     * @return Result
     */
    public function success($errMsg = 'ok', $data = [])
    {
        return Result::instance()->setSuccess()->setErrmsg($errMsg)->setData($data);
    }

    /**
     * 失败返回
     * @param int $errCode 错误码
     * @param string $errMsg 提示信息
     * @param array $data 响应数据
     * @return Result
     */
    public function error($errCode, $errMsg = '', $data = [])
    {
        return Result::instance()->setErrcode($errCode)->setErrmsg($errMsg)->setData($data);
    }
}