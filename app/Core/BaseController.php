<?php


namespace App\Core;

use App\Constants\StatusCode;
use App\Core\Components\Response;
use App\Core\Components\Result;
use App\Controller\AbstractController;

class BaseController extends AbstractController
{
    /**
     * 自动判断成功或失败，并返回结果
     * @param Result $result
     * @param string $format
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function autoReturn(Result $result, $format = Response::FORMAT_JSON)
    {
        return $this->renderApi($result->getErrcode(), $result->getErrmsg(), $result->getData(), $format);
    }

    /**
     * 成功返回
     * @param string $errMsg 提示信息
     * @param array $data 响应数据
     * @param string $format 响应格式
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function success($errMsg = '', $data = [], $format = Response::FORMAT_JSON)
    {
        return $this->renderApi(StatusCode::SUCCESS, $errMsg, $data, $format);
    }

    /**
     * 失败返回
     * @param int $errCode 错误码
     * @param string $errMsg 提示信息
     * @param array $data 响应数据
     * @param string $format 响应格式
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function error($errCode, $errMsg = '', $data = [], $format = Response::FORMAT_JSON)
    {
        return $this->renderApi($errCode, $errMsg, $data, $format);
    }

    /**
     * @param $errCode
     * @param string $errMsg
     * @param array $data
     * @param string $format
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function renderApi($errCode, $errMsg = '', $data = [], $format = Response::FORMAT_JSON)
    {
        $errMsg = $errMsg ? $errMsg : StatusCode::instance()->getMessage($errCode);
        if (is_array($errMsg)) {
            list($errCode, $errMsg) = $errMsg;
        }
        return Response::instance()->send($errCode, $data, $errMsg, $format);
    }
}