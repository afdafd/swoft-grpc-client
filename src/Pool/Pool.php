<?php declare(strict_types=1);


namespace Hzwz\Grpc\Client\Pool;

use Hzwz\Grpc\Client\Client as GrpcClient;
use Hzwz\Grpc\Client\Exception\GrpcClientException;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\BeanFactory;
use Swoft\Connection\Pool\Contract\ConnectionInterface;
use Swoft\Event\EventInterface;
use Swoft\Log\Helper\Log;

/**
 * Class Pool
 * @package Pool
 */
class Pool
{
    /**
     * @var GrpcClient
     */
    protected $client;

    /**
     * @var string[]
     */
    private $grpcClientPools = [
        CarUserCenterPool::class,
        CarPayCenterPool::class,
        CarDeviceCenterPool::class,
        CarPublicCenterPool::class,
        CarGatewayCenterPool::class,
        CarHwbCenterPool::class,
    ];

    /**
     * 初始化
     */
    public function initPools()
    {
        foreach($this->grpcClientPools as $pool) {
            BeanFactory::getBean($pool)->baseInit($this->client);
        }
    }

    /**
     * 关闭连接
     */
    public function closeConnect(EventInterface $event)
    {
        $closePool = '';
        foreach($this->grpcClientPools as $pool) {
            $count = BeanFactory::getBean($pool)->close();
            $closePool .= (string)$pool .':'. $count . PHP_EOL;
        }

        Log::info('grpcClientClose: 关闭grpc客户端连接', [
          'eventName'   => $event->getName(),
          'eventParams' => $event->getParams(),
          'closeDetail' => $closePool,
          'closeTime'   => date("Y-m-d H:i:s"),
      ]);
    }
}
