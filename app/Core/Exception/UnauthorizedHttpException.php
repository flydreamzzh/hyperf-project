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
namespace App\Core\Exception;

/**
 * UnauthorizedHttpException represents an "Unauthorized" HTTP exception with status code 401.
 *
 * Use this exception to indicate that a client needs to authenticate via WWW-Authenticate header
 * to perform the requested action.
 *
 * If the client is already authenticated and is simply not allowed to
 * perform the action, consider using a 403 [[ForbiddenHttpException]]
 * or 404 [[NotFoundHttpException]] instead.
 *
 * @see https://tools.ietf.org/html/rfc7235#section-3.1
 */
class UnauthorizedHttpException extends HttpException
{
    /**
     * Constructor.
     * @param null $message error message
     * @param int $code error code
     * @param null|\Exception $previous the previous exception used for the exception chaining
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(401, $message, $code, $previous);
    }
}
