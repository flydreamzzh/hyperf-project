<?php
namespace App\Task;

use App\Core\BaseTask;
use App\Services\AlarmService;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

/**
 * @Crontab(name="Log", rule="* * * * *", callback="execute", memo="这是一个示例的定时任务")
 */
class LogEmailTask extends BaseTask
{

    /**
     * @Inject()
     * @var \Hyperf\Contract\StdoutLoggerInterface
     */
    private $logger;

    public function task()
    {
        AlarmService::sendAlarm();
    }
}