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
namespace App\Exception\Handler;

use App\Core\Components\Log;
use App\Core\Exception\HttpException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Throwable $throwable
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $statusCode = 500;
        if ($throwable instanceof HttpException) {
            $data = [
                'code' => 0,
                'message' => $throwable->getMessage(),
                'name' => $throwable->getName(),
                'status' => $throwable->statusCode,
            ];
            $statusCode = $throwable->statusCode;
        } else {
            $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
            $this->logger->error($throwable->getTraceAsString());
            Log::error($throwable); //除http协议外的异常都做记录
            $data = [
                'code' => 0,
                'message' => 'Internal Server Error.',
                'name' => 'Exception',
                'status' => $statusCode,
            ];
        }
        return $response
            ->withStatus($statusCode)
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream(json_encode($data, JSON_UNESCAPED_UNICODE)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
