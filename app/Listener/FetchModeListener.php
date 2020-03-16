<?php
declare(strict_types=1);

namespace App\Listener;

use Hyperf\Database\Events\StatementPrepared;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use PDO;
/**
 * Db::select() 方法会返回一个 array，而 get 方法会返回 Hyperf\Utils\Collection。其中元素是 stdClass
 * 将结果转为数组格式在某些场景下，您可能会希望查询出来的结果内采用 数组(Array) 而不是 stdClass 对象结构时，
 * 而 Eloquent 又去除了通过配置的形式配置默认的 FetchMode，
 * 那么此时可以通过监听器来监听 Hyperf\Database\Events\StatementPrepared 事件来变更该配置
 * @Listener
 */
class FetchModeListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            StatementPrepared::class,
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof StatementPrepared) {
            $event->statement->setFetchMode(PDO::FETCH_ASSOC);
        }
    }
}