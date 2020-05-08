<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Components\Identity;
use App\Core\Exception\ForbiddenHttpException;
use App\Core\Exception\UnauthorizedHttpException;
use App\Core\Helpers\ArrayHelper;
use App\Model\Rbac\RbacPermission;
use App\Model\User;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 权限验证
 * Class AuthMiddleware
 * @package App\Middleware
 */
class AuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var User User for check access.
     */
    private $_user = 'user';

    /**
     * 不用登录也能 访问的地址
     * @var array List of action that not need to check access.
     */
    public $allowActions = [];

    /**
     * 以登录为前提条件，所允许的地址
     * @var array
     */
    public $loginActions = [];

    /**
     * 不存在权限的路由  是否 直接放通。
     * @var bool
     */
    public $defaultAccess = false;

    public function __construct(ContainerInterface $container, RequestInterface $request)
    {
        $this->container = $container;
        $this->request = $request;
        $this->allowActions = config('permission.allowActions') ?? [];
        $this->loginActions = config('permission.loginActions') ?? [];
        $this->defaultAccess = config('permission.defaultAccess') ?? $this->defaultAccess;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ForbiddenHttpException
     * @throws UnauthorizedHttpException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->accessControl();
        return $handler->handle($request);
    }

    /**
     * @return bool|void
     * @throws ForbiddenHttpException
     * @throws UnauthorizedHttpException
     */
    public function accessControl()
    {
        //每时每刻判断用户是否被禁用
        $user = $this->getUser();
        if ($user && $curUser = User::findOne(['id' => $user->id])) {
            if ($curUser->status != User::STATUS_ACTIVE) {
                throw new ForbiddenHttpException("此用户已被禁用，没有权限进行此操作");
            }
        }
        $url = $this->request->getUri()->getPath();
        //登录状态才能访问
        if ($this->getIdentity()->getIsGuest() && RbacPermission::isExcept($url, $this->loginActions)) {
            return $this->denyAccess($this->getIdentity());
        }
        //直接通过
        if (RbacPermission::isExcept($url, ArrayHelper::merge($this->allowActions, $this->loginActions))) {
            return true;
        }
        if ($this->getIdentity()->getIsGuest()) {
            return $this->denyAccess($this->getIdentity());
        }
        if (RbacPermission::can($user->id, $url, [], $this->defaultAccess)) {
            return true;
        }
        return $this->denyAccess($this->getIdentity());
    }

    /**
     * Get user
     * @return User
     */
    public function getUser()
    {
        if (!$this->_user instanceof User) {
            $this->_user = $this->getIdentity()->getIdentity();
        }
        return $this->_user;
    }

    /**
     * Denies the access of the user.
     * The default implementation will redirect the user to the login page if he is a guest;
     * if the user is already logged, a 403 HTTP exception will be thrown.
     * @param Identity $user the current user
     * @throws UnauthorizedHttpException
     * @throws ForbiddenHttpException if the user is already logged in.
     */
    protected function denyAccess($user)
    {
        if ($user->getIsGuest()) {
            throw new UnauthorizedHttpException('用户需要登录');
        } else {
            throw new ForbiddenHttpException("用户没有权限进行此操作");
        }
    }

    /**
     * @return Identity|mixed|null
     */
    private function getIdentity()
    {
        return Context::get(Identity::class);
    }

}