<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Components\Log;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Utils\Context;

/**
 * RequestMiddleware
 * 接到客户端请求，通过该中间件进行一些调整
 * @package App\Middleware
 * User：YM
 * Date：2019/12/16
 * Time：上午12:13
 */
class RequestMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    public function __construct(ContainerInterface $container, ServerRequestInterface $request)
    {
        $this->container = $container;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 利用协程上下文存储请求开始的时间，用来计算程序执行时间
        Context::set('request_start_time', microtime(true));
        $response = $handler->handle($request);
        //记录日志
        $executionTime = microtime(true) - Context::get('request_start_time');
        $queryParams = $request->getBody()->getContents();
        $result = $response->getBody()->getContents();
        $logInfo = implode(' | ', [$executionTime, $queryParams, $result]);
        Log::info($logInfo);
        return $response;
    }
}