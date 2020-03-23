<?php
namespace App\Task;

use App\Core\BaseTask;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

/**
 * @注释Crontab(name="Foo", rule="*\/5 * * * * *", callback="execute", memo="这是一个示例的定时任务")
 */
class FooTask extends BaseTask
{

    /**
     * @Inject()
     * @var \Hyperf\Contract\StdoutLoggerInterface
     */
    private $logger;

    public function task()
    {
        $this->logger->info(date('Y-m-d H:i:s', time()));
    }
}