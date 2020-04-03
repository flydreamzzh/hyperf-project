## 系统运行日志告警发送

日志文件分类
```php

dependencies.php文件：
return [
    //配置日志类LoggerFactory的指向，解决日志Log::error($throwable)传入异常后，自动序列化（string）的问题
    \Hyperf\Logger\LoggerFactory::class => \App\Core\Dependence\MyLoggerFactory::class,//替换addRecord方法，不序列化(string)日志信息
];

/**
 * 配置文件格式，重新分封装LogFileHandler，使用channel对日志进行大分类
 * 重新定义stream属性，使日志文件按日期进行创建
 * 设置不同日志的格式化类
 */
[
    'class' => App\Core\Handler\LogFileHandler::class,
    'constructor' => [
        'stream' => BASE_PATH . '/runtime/logs/mailer/error/[datetime].log',
        'level' => Monolog\Logger::ERROR,
        'channel' => 'mailer'
    ],
    'formatter' => [
        'class' => \App\Core\Formatter\MailerLineFormatter::class,
        'constructor' => [
            'format' => null,
            'dateFormat' => null,
            'allowInlineLineBreaks' => true,
        ],
    ]
],
```

```
/**
 * 日志格式化，添加额外的字段
 * Class LineFormatter
 * @package App\Base\Formatter
 */
class LineFormatter extends BaseLineFormatter
{
    /**
     * @Inject()
     * @var RequestInterface $request
     */
    protected $request;

    const NEW_FORMAT = "[%datetime%][%appid%][%pid%][%session_id%][%channel%][%level_name%][%ip%][%uri%][%method%] %message% %context% %extra%\n";
}
```
