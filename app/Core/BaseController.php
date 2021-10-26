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
namespace App\Core;

use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Core\Components\Response;
use App\Core\Components\Result;
use Psr\Http\Message\ResponseInterface;

class BaseController extends AbstractController
{
    /**
     * 自动判断成功或失败，并返回结果.
     * @param string $format
     * @return ResponseInterface
     */
    public function autoReturn(Result $result, $format = Response::FORMAT_JSON)
    {
        return $this->renderApi($result->getErrcode(), $result->getErrmsg(), $result->getData(), $format);
    }

    /**
     * 成功返回.
     * @param string $errMsg 提示信息
     * @param array $data 响应数据
     * @param string $format 响应格式
     * @return ResponseInterface
     */
    public function success($data = [], $errMsg = '', $format = Response::FORMAT_JSON)
    {
        return $this->renderApi(StatusCode::SUCCESS, $errMsg, $data, $format);
    }

    /**
     * 失败返回.
     * @param int|string $errCode 错误码
     * @param string $errMsg 提示信息
     * @param array $data 响应数据
     * @param string $format 响应格式
     * @return ResponseInterface
     */
    public function error($errCode, $errMsg = '', $data = [], $format = Response::FORMAT_JSON): ResponseInterface
    {
        return $this->renderApi($errCode, $errMsg, $data, $format);
    }

    /**
     * @param int|string $errCode
     * @param string $errMsg
     * @param array $data
     * @param string $format
     * @return ResponseInterface
     */
    public function renderApi($errCode, $errMsg = '', $data = [], $format = Response::FORMAT_JSON): ResponseInterface
    {
        $errMsg = $errMsg ? $errMsg : StatusCode::instance()->getMessage($errCode);
        if (is_array($errMsg)) {
            [$errCode, $errMsg] = $errMsg;
        }
        return Response::instance()->send($errCode, $data, $errMsg, $format);
    }
}
