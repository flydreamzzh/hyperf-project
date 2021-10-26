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
namespace App\Services;

use App\Core\BaseService;
use App\Core\Components\EmailSender;
use App\Core\Components\Log;
use App\Core\Helpers\DateHelper;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Parallel;

/**
 * 报警发送服务
 * Class AlarmService.
 */
class AlarmService extends BaseService
{
    /** @var string 是否开启告警 */
    const ALARM_STATUS_KEY = 'SYS_ALARM_STATUS';

    /** @var string 告警邮箱配置项 */
    const ALARM_EMAILS_KEY = 'SYS_ALARM_EMAILS';

    /** @var string 统计记录 */
    const ALARM_KEY = 'alarm_notify';

    /** @var string 状态记录 */
    const ALARM_KEY_STATUS = 'alarm_notify_status';

    /**
     * 发送时间段.
     * @var array
     */
    public static $sendTimes = [
        1 => 0, //第一次发送即时发送
        2 => 3, //第二次发送时离第一次需要相隔3分钟
        3 => 5, //第三次发送相隔5分钟
        4 => 10, //第四次之后相隔10分钟
        5 => 15, //第五次之后相隔15分钟
        6 => 30, //第六次之后相隔30分钟
        7 => 60, //第七次之后相隔一小时
    ];

    /**
     * 添加告警，一周统计一次
     * @param string $key 告警标识
     * @param string $subject 标题
     * @param string $content 内容
     */
    public static function addAlarm($key, $subject, $content)
    {
        $curTime = date('Y-m-d H:i:s');
        if (! $redis = static::getRedis()) {
            echo 'Redis 链接失败！' . PHP_EOL;
            return;
        }
        $data = [
            'subject' => $subject,
            'content' => $content,
            'datetime' => $curTime,
        ];

        //一周一个统计
        $firstDay = strtotime(DateHelper::weekFirstDay());
        $dateTime = $redis->hget(self::ALARM_KEY_STATUS, 'dayTime');
        if ($dateTime != $firstDay) {
            $redis->exists(self::ALARM_KEY) && $redis->del(self::ALARM_KEY);
            $redis->exists(self::ALARM_KEY_STATUS) && $redis->del(self::ALARM_KEY_STATUS);
            $redis->hset(self::ALARM_KEY_STATUS, 'dayTime', $firstDay);
        }

        $redisKey = self::ALARM_KEY . md5($key);
        $redis->hMSet($redisKey, $data);

        if ($redis->hincrby(self::ALARM_KEY, $redisKey, 1) == 1) {
            $redis->hsetnx(self::ALARM_KEY_STATUS, $redisKey . 'start_datetime', $curTime);
        }
        //设置2小时过期
        $redis->expire($redisKey, 7200);
    }

    /**
     * 发送告警邮件.
     */
    public static function sendAlarm()
    {
        if (! $redis = static::getRedis()) {
            echo 'Redis 链接失败！';
            return;
        }
        $curTime = time();
        while (time() - $curTime < 60) {//循环一分钟，定时任务1分钟
            $alarmKeys = $redis->hkeys(self::ALARM_KEY);
            if ($alarmKeys) {
                //使用WaitGroup特性，保证每次循环都不相互干扰，若不使用此特性，由于redis协程的原因，判断出问题，会导致同一个告警多发，且发送的内容为空
                $parallel = new Parallel();
                foreach ($alarmKeys as $alarmKey) {
                    //存在告警队列
                    if ($redis->exists($alarmKey)) {
                        $latestTime = $redis->hget(self::ALARM_KEY_STATUS, $alarmKey . 'sendAt');
                        $sendTimes = $redis->hget(self::ALARM_KEY_STATUS, $alarmKey . 'sendTimes');
                        $sendTimes = $sendTimes ? $sendTimes : 0;
                        $nextSendTime = self::getNextTime((int) $latestTime, $sendTimes);

                        if (empty($latestTime) || $nextSendTime < time()) {
                            //锁定当前告警
                            if ($redis->hSetNx($alarmKey, 'locked', time())) {
                                //开启异步协程

                                $parallel->add(function () use ($redis, $alarmKey, $sendTimes) {
                                    $alarmSubject = $redis->hget($alarmKey, 'subject');
                                    $alarmContent = $redis->hget($alarmKey, 'content');
                                    $alarmCurDatetime = $redis->hget($alarmKey, 'datetime');

                                    $alarmQty = $redis->hget(self::ALARM_KEY, $alarmKey); //出现次数
                                    $start_datetime = $redis->hget(self::ALARM_KEY_STATUS, $alarmKey . 'start_datetime'); //首次出现时间

                                    try {
                                        $times = ! empty($latestTime) ? $sendTimes + 1 : 1;
                                        $head = "【本周】首次记录时间：{$start_datetime}，最新记录时间：{$alarmCurDatetime}，记录次数：{$alarmQty}。已发送次数：{$times}";
                                        $content = implode("\n\n", [$head, $alarmContent]);
                                        if (! $result = self::sendEmail($alarmSubject, $content)) {
                                            if ($redis->hSetNx(self::ALARM_KEY_STATUS, $alarmKey . 'failAt', time())) {
                                                Log::mailerLog()->error($alarmSubject . "[{$alarmKey}]::发送失败====" . str_replace(["\r\n", "\n", "\r"], ' -> ', $alarmContent));
                                            }
                                        } else {
                                            //成功则删除告警队列
                                            $redis->del($alarmKey);
                                            $redis->hset(self::ALARM_KEY_STATUS, $alarmKey . 'sendAt', time());
                                            $redis->hincrby(self::ALARM_KEY_STATUS, $alarmKey . 'sendTimes', 1);
                                        }
                                        echo $alarmSubject . "[{$alarmKey}]::发送完成，结果：" . ($result ? 'success' : 'fail') . PHP_EOL;
                                    } catch (\Throwable $e) {
                                        if ($redis->hSetNx(self::ALARM_KEY_STATUS, $alarmKey . 'failAt', time())) {
                                            Log::mailerLog()->error($alarmSubject . "[{$alarmKey}]::发送异常【" . $e->getMessage() . '】====' . str_replace(["\r\n", "\n", "\r"], ' -> ', $alarmContent));
                                        }
                                        echo $alarmSubject . "[{$alarmKey}]::发送异常" . $e->getMessage() . PHP_EOL;
                                    }
                                });
                            } else {
                                //告警发送被锁
                                $alarmSubject = $redis->hget($alarmKey, 'subject');
                                $alarmContent = $redis->hget($alarmKey, 'content');
                                if ($redis->hSetNx(self::ALARM_KEY_STATUS, $alarmKey . 'failAt', time())) {
                                    Log::mailerLog()->error($alarmSubject . "[{$alarmKey}]::告警被锁，发送失败====" . str_replace(["\r\n", "\n", "\r"], ' -> ', $alarmContent));
                                }
                                $lockAt = $redis->hGet($alarmKey, 'locked'); //锁定60秒
                                if (time() - $lockAt > 60) {
                                    //锁住60秒，删除锁标识，防止该消息不推送
                                    $redis->hdel($alarmKey, 'locked');
                                }
                            }
                        }
                    }
                }
                $parallel->wait();
            }
        }
    }

    /**
     * 获取下次可发送时间.
     * @param int $latestTime
     * @param int $sendQty
     * @return float|int
     */
    public static function getNextTime(int $latestTime, int $sendQty)
    {
        $nextQty = $sendQty + 1;
        $item = isset(self::$sendTimes[$nextQty]) ? self::$sendTimes[$nextQty] : end(self::$sendTimes);
        return $latestTime + $item * 60;
    }

    /**
     * @param string $subject 标题
     * @param string $content 内容
     * @throws \PHPMailer\PHPMailer\Exception
     * @return mixed
     */
    protected static function sendEmail($subject, $content)
    {
        $emails = [];
        $email = EmailSender::instance();
        return $email->setSubject($subject)
            ->setTo($emails)->setTextBody($content)->send();
    }

    /**
     * 获取redis.
     * @return bool|mixed|\Redis
     */
    private static function getRedis()
    {
        try {
            /** @var \Redis $redis */
            $redis = ApplicationContext::getContainer()->get(Redis::class);
            if ($redis->ping() === true) {
                return $redis;
            }
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 返回hmset的参数.
     * @param $name string hmset的名称
     * @param $data array hmset的参数
     * @return array
     */
    private static function setRedisHMSetData(string $name, array $data): array
    {
        $newData = [];
        foreach ($data as $key => $val) {
            $newData[] = $key;
            $newData[] = $val;
        }
        return array_merge([$name], $newData);
    }
}
