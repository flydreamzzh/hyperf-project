<?php


namespace App\Core;


use App\Core\Components\Log;
/**
 * 定时任务基础类，加入日志记录
 * Class BaseTask
 * @package App\Core
 */
abstract class BaseTask
{
    /**
     * @throws \Throwable
     */
    public function execute()
    {
        try {
            call([$this, 'task']);
            $prefix = $this->getLogPrefix();
            $message = implode(' | ', [$prefix, 'SUCCESS']);
            Log::get('command')->error($message);
        } catch (\Throwable $exception) {
            $prefix = $this->getLogPrefix();
            $message = sprintf('%s[%s] in %s',$exception->getMessage(), $exception->getFile(), $exception->getLine());
            $message = implode(' | ', [$prefix, $message]);
            Log::get('command')->error($message);
            Log::stdLog()->error($message . PHP_EOL . $exception->getTraceAsString());
            throw $exception;
        }
    }

    /**
     * @return mixed
     */
    abstract public function task();

    /**
     * @return string
     */
    protected function getLogPrefix()
    {
        $name = static::class;
        return $name;
    }
}