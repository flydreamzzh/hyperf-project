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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 注释Crontab(name="Demo", rule="*\/5 * * * * *", callback="execute", memo="这是一个示例的定时任务").
 */
class DemoTask extends BaseTask
{
    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function task()
    {
        $this->logger->info(date('Y-m-d H:i:s', time()));
    }
}
