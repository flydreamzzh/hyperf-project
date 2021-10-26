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

use App\Core\Components\Result;

trait ResultTrait
{
    /**
     * 成功返回.
     * @param string $errMsg 提示信息
     * @param array $data 响应数据
     * @return Result
     */
    public function success($data = [], $errMsg = 'ok')
    {
        return (new Result())->setSuccess()->setErrmsg($errMsg)->setData($data);
    }

    /**
     * 失败返回.
     * @param int $errCode 错误码
     * @param string $errMsg 提示信息
     * @param array $data 响应数据
     * @return Result
     */
    public function error($errCode, $errMsg = '', $data = [])
    {
        return (new Result())->setErrcode($errCode)->setErrmsg($errMsg)->setData($data);
    }
}
