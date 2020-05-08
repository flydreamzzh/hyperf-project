<?php


namespace App\Exception\Handler;

use App\Core\Components\Response;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * 获取请求验证失败的数据
 * Class ValidationExceptionHandler
 * @package App\Exception\Handler
 */
class ValidationExceptionHandler  extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->stopPropagation();
        /** @var \Hyperf\Validation\ValidationException $throwable */
        $body = $throwable->validator->errors()->first();
        return Response::instance()->send($body);
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ValidationException;
    }
}