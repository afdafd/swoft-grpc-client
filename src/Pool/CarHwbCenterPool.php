<?php declare(strict_types=1);

namespace Hzwz\Grpc\Client\Pool;


use Hzwz\Grpc\Client\Client as GrpcClient;
use Hzwz\Grpc\Client\Exception\GrpcClientException;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Connection\Pool\Contract\ConnectionInterface;
use Swoft\Connection\Pool\Exception\ConnectionPoolException;

/**
 * Class CarHwbCenterPool
 * @package Hzwz\Grpc\Client\Pool
 *
 * @Bean("CarHwbCenterPool")
 */
class CarHwbCenterPool extends AbstractPool implements GrpcClientPoolInterface
{
    /**
     * @var string
     */
    protected $clientName = 'carHwbCenter';

    /**
     * @var GrpcClient
     */
    protected $client;

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * 初始化
     *
     * @param GrpcClient $client
     * @throws ConnectionPoolException
     */
    public function baseInit(GrpcClient $client)
    {
        $this->client = $client;

        $this->initCustomConfig();
        $this->initPool();
    }

    /**
     * 创建连接池连接
     *
     * @return ConnectionInterface
     */
    public function createConnection(): ConnectionInterface
    {
        if (empty($this->client)) {
            throw new GrpcClientException(sprintf('连接池(%s) 的客户端不能为空!', static::class));
        }

        return $this->client->createConnection($this, $this->clientName);
    }

    /**
     * 初始化自定义配置
     */
    private function initCustomConfig()
    {
        $config = $this->client->getCarHwbCenter();

        if (empty($config)) {
            return;
        }

        $customConfig = array();
        if (isset($config['options']) && !empty($config['options'])) {
            $customConfig = $config['options'];
        } elseif (!empty($this->client->getOptions())) {
            $customConfig = $this->client->getOptions();
        } else {
            return;
        }

        foreach ($customConfig as $field => $value) {
            $this->$field = $value;
        }
    }
}
