<?php declare(strict_types=1);


namespace Hzwz\Grpc\Client;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoole\Coroutine\Channel;

/**
 * Class Pool
 * @package GrpcClient
 *
 * @Bean()
 */
class ChannelPool extends \SplQueue
{
    /**
     * 获取一个Channel
     *
     * @return Channel
     */
    public function get(): Channel
    {
        return $this->isEmpty() ? new Channel(2) : $this->pop();
    }
}
