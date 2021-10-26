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
namespace App\Core\Formatter;

use App\Services\AlarmService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Context;
use Monolog\Formatter\LineFormatter as BaseLineFormatter;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface;

/**
 * 日志格式化，添加额外的字段
 * Class LineFormatter.
 */
class LineFormatter extends BaseLineFormatter
{
    const NEW_FORMAT = "[%datetime%][%appid%][%pid%][%session_id%][%channel%][%level_name%][%ip%][%uri%][%method%] %message% %context% %extra%\n";

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * LineFormatter constructor.
     * @param null $format
     * @param null $dateFormat
     * @param bool $allowInlineLineBreaks
     * @param bool $ignoreEmptyContextAndExtra
     */
    public function __construct($format = null, $dateFormat = null, $allowInlineLineBreaks = false, $ignoreEmptyContextAndExtra = false)
    {
        $format = $format ? $format : self::NEW_FORMAT;
        parent::__construct($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
    }

    /**
     * @return null|array|mixed|string
     */
    public function format(array $record): string
    {
        $result = parent::format($record); // TODO: Change the autogenerated stub
        $appId = env('APP_NAME', '-');
        $pid = getmypid();
        $sessionId = '-';
        $ip = '-';
        $uri = '-';
        $method = '-';
        if (Context::get(ServerRequestInterface::class)) {
            $params = $this->request->getServerParams();
            $ip = isset($params['remote_addr']) ? $params['remote_addr'] : '-';
            $uri = $this->request->getRequestTarget();
            $method = $this->request->getMethod();
        }
        $result = preg_replace(['/%appid%/', '/%pid%/', '/%session_id%/', '/%ip%/', '/%uri%/', '/%method%/'], [$appId, $pid, $sessionId, $ip, $uri, $method], $result);

        if ($record['level'] >= Logger::ERROR) {
            $alarmMessage = $result;
            $key = is_object($record['message']) ? $this->toJson($record['message'], true) : $record['message'];
            if ($record['message'] instanceof \Throwable && ! $this->includeStacktraces) {
                $alarmMessage = sprintf("%s\nStack trace:\n%s", $result, $record['message']->getTraceAsString());
                $key = sprintf('%s[%s] in %s', $record['message']->getMessage(), $record['message']->getLine(), $record['message']->getFile());
            }
            AlarmService::addAlarm($key, '[报错通知]', $alarmMessage);
        }
        return $result;
    }
}
