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
namespace App\Middleware;

use App\Core\Components\Identity;
use App\Core\Components\Log;
use App\Model\User;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * RequestMiddleware
 * 接到客户端请求，通过该中间件进行一些调整.
 */
class RequestMiddleware implements MiddlewareInterface
{
    /**
     * @var string the parameter name for passing the access token
     */
    public $tokenParam = 'access_token';

    /**
     * @var string the HTTP header name
     */
    public $header = 'X-Api-Key';

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
        $this->authenticate();
        $response = $handler->handle($request);
        //记录日志
        $executionTime = microtime(true) - Context::get('request_start_time');
        $queryParams = $request->getBody()->getContents();
        $result = $response->getBody()->getContents();
        $logInfo = implode(' | ', [$executionTime, $queryParams, $result]);
        Log::info($logInfo);
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate()
    {
        Context::set(Identity::class, make(Identity::class));
        Context::override(Identity::class, function (Identity $identity) {
            $accessToken = $this->request->post($this->tokenParam); //post，比较安全
            $params = $this->request->getServerParams();
            $ip = isset($params['remote_addr']) ? $params['remote_addr'] : '';
            if (! $accessToken) {
                $headerName = config('user.accessTokenHeader') ?? $this->header;
                $accessToken = $this->request->getHeaderLine($headerName);
            }
            if (is_string($accessToken) && User::validateAccessToken($accessToken, $ip)) {
                $identity->loginByAccessToken($accessToken, get_class($this));
            } else {
                $identity->setIdentity(null);
            }
            return $identity;
        });
    }
}
