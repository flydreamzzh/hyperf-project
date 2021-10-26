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
namespace App\Task;

use App\Core\BaseTask;
use App\Services\AlarmService;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

/**
 * @Crontab(name="Log", rule="* * * * *", callback="execute", memo="这是一个示例的定时任务")
 */
class LogEmailTask extends BaseTask
{
    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function task()
    {
        AlarmService::sendAlarm();
    }
}
