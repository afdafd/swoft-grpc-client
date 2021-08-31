<?php declare(strict_types=1);

namespace Hzwz\Grpc\Client\Listener;

use Hzwz\Grpc\Client\Pool\CarUserCenterPool;
use Hzwz\Grpc\Client\Pool\Pool;
use Swoft\Bean\BeanFactory;
use Swoft\Event\Annotation\Mapping\Subscriber;
use Swoft\Event\EventInterface;
use Swoft\Event\EventSubscriberInterface;
use Swoft\Log\Helper\Log;
use Swoft\Server\SwooleEvent;
use Swoft\SwoftEvent;

/**
 * Class WorkerStopAndErrorListener
 *
 * @since 2.0
 *
 * @Subscriber()
 */
class WorkerStopAndErrorListener implements EventSubscriberInterface
{
    /**
     * 订阅事件（worker_stop和worker_shutdown）
     *
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SwooleEvent::WORKER_STOP    => 'handle',
            SwoftEvent::WORKER_SHUTDOWN => 'handle',
        ];
    }

    /**
     * 事件处理（关闭连接池里的连接）
     *
     * @param EventInterface $event
     */
    public function handle(EventInterface $event): void
    {
        BeanFactory::getBean(Pool::class)->closeConnect($event);
    }
}
