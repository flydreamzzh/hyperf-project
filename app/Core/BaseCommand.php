<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Core;

use App\Core\Components\Log;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Event\AfterExecute;
use Hyperf\Command\Event\AfterHandle;
use Hyperf\Command\Event\BeforeHandle;
use Hyperf\Command\Event\FailToHandle;
use Hyperf\Utils\Coroutine;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * 命令行基础类，加入日志记录
 * Class BaseCommand
 * @package App\Core
 */
abstract class BaseCommand extends HyperfCommand
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $callback = function () {
            try {
                $this->eventDispatcher && $this->eventDispatcher->dispatch(new BeforeHandle($this));
                call([$this, 'handle']);
                $this->eventDispatcher && $this->eventDispatcher->dispatch(new AfterHandle($this));
                $prefix = $this->getLogPrefix();
                $message = implode(' | ', [$prefix, 'SUCCESS']);
                Log::get('command')->info($message);
            } catch (\Throwable $exception) {
                $prefix = $this->getLogPrefix();
                $message = sprintf('%s[%s] in %s',$exception->getMessage(), $exception->getFile(), $exception->getLine());
                $message = implode(' | ', [$prefix, $message]);
                Log::get('command')->error($message);
                if (! $this->eventDispatcher) {
                    throw $exception;
                }

                $this->eventDispatcher->dispatch(new FailToHandle($this, $exception));
                return $exception->getCode();
            } finally {
                $this->eventDispatcher && $this->eventDispatcher->dispatch(new AfterExecute($this));
            }

            return 0;
        };

        if ($this->coroutine && ! Coroutine::inCoroutine()) {
            run($callback, $this->hookFlags);
            return 0;
        }

        return $callback();
    }

    /**
     * @return string
     */
    protected function getLogPrefix()
    {
        $argc = $this->input->getArguments();
        $command = implode(' ', $argc);
        return $command;
    }
}
