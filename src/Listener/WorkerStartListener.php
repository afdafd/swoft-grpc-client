<?php declare(strict_types=1);


namespace Hzwz\Grpc\Client\Listener;

use Hzwz\Grpc\Client\Client;
use Hzwz\Grpc\Client\Pool\CarUserCenterPool;
use Hzwz\Grpc\Client\Pool\Pool;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\BeanFactory;
use Swoft\Config\Config;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\Server\ServerEvent;
use Swoft\Event\Annotation\Mapping\Listener;

/**
 * Class WorkerStartListener
 * @package Hzwz\Grpc\Client\Listener
 *
 * @Listener(event=ServerEvent::WORK_PROCESS_START)
 */
class WorkerStartListener implements EventHandlerInterface
{
    /**
     * Grpc client事件处理
     *
     * @param EventInterface $event
     */
    public function handle(EventInterface $event): void
    {
        BeanFactory::getBean(Pool::class)->initPools();
    }
}
