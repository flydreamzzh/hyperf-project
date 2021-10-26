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
 * ForbiddenHttpException represents a "Forbidden" HTTP exception with status code 403.
 *
 * Use this exception when a user is not allowed to perform the requested action.
 * Using different credentials might or might not allow performing the requested action.
 * If you do not want to expose authorization information to the user, it is valid
 * to respond with a 404 [[NotFoundHttpException]].
 *
 * @see https://tools.ietf.org/html/rfc7231#section-6.5.3
 */
class ForbiddenHttpException extends HttpException
{
    /**
     * Constructor.
     * @param null $message error message
     * @param int $code error code
     * @param null|\Exception $previous the previous exception used for the exception chaining
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(403, $message, $code, $previous);
    }
}
