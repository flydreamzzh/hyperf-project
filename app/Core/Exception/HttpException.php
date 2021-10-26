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

use App\Core\Components\Response;

class HttpException extends \Exception
{
    /**
     * @var int HTTP status code, such as 403, 404, 500, etc
     */
    public $statusCode;

    /**
     * Constructor.
     * @param int $status HTTP status code, such as 404, 500, etc
     * @param null $message error message
     * @param int $code error code
     * @param null|\Exception $previous the previous exception used for the exception chaining
     */
    public function __construct($status, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->statusCode = $status;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName(): string
    {
        if (isset(Response::$httpStatuses[$this->statusCode])) {
            return Response::$httpStatuses[$this->statusCode];
        }

        return 'Error';
    }
}
