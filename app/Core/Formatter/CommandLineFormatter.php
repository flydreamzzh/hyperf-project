<?php


namespace App\Core\Formatter;

use App\Services\AlarmService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use \Monolog\Formatter\LineFormatter as BaseLineFormatter;
use Monolog\Logger;

/**
 * 日志格式化，添加额外的字段
 * Class LineFormatter
 * @package App\Base\Formatter
 */
class CommandLineFormatter extends BaseLineFormatter
{
    /**
     * @Inject()
     * @var RequestInterface $request
     */
    protected $request;

    const NEW_FORMAT = "[%datetime%][%appid%][%pid%][%user%][%channel%][%level_name%] %message%\n";

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
     * @param array $record
     * @return array|mixed|string|null
     */
    public function format(array $record)
    {
        $result = parent::format($record); // TODO: Change the autogenerated stub
        $appId = env('APP_NAME', '-');
        $pid = getmypid();
        $user = get_current_user();
        $result = preg_replace(['/%appid%/', '/%pid%/', '/%user%/'], [$appId, $pid, $user], $result);

        if ($record['level'] >= Logger::ERROR) {
            $alarmMessage = $result;
            $key = is_object($record['message']) ? $this->toJson($record['message'], true) : $record['message'];
            if ($record['message'] instanceof \Throwable && !$this->includeStacktraces) {
                $alarmMessage = sprintf("%s\nStack trace:\n%s", $result, $record['message']->getTraceAsString());
                $key = sprintf('%s[%s] in %s', $record['message']->getMessage(), $record['message']->getLine(), $record['message']->getFile());
            }
            AlarmService::addAlarm($key, "[报错通知][命令行]", $alarmMessage);
        }
        return $result;
    }
}