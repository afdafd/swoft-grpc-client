<?php declare(strict_types=1);


namespace Hzwz\Grpc\Client\Pool;


use Hzwz\Grpc\Client\Client;
use Hzwz\Grpc\Client\Exception\GrpcClientException;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Connection\Pool\Contract\ConnectionInterface;
use Swoft\Connection\Pool\Exception\ConnectionPoolException;

/**
 * Class CarGatewayCenterPool
 * @package Hzwz\Grpc\Client\Pool
 *
 * @Bean("CarGatewayCenterPool")
 */
class CarGatewayCenterPool extends AbstractPool implements GrpcClientPoolInterface
{
    /**
     * @var string
     */
    protected $clientName = 'carGatewayCenter';

    /**
     * @param Client $client
     * @throws ConnectionPoolException
     */
    public function baseInit(Client $client)
    {
        $this->initCustomConfig($client);
        $this->initPool();
    }

    /**
     * 创建连接池连接
     *
     * @return ConnectionInterface
     */
    public function createConnection(): ConnectionInterface
    {
        $grpcClient = \bean('grpcClients');

        if (empty($grpcClient)) {
            throw new GrpcClientException(sprintf('连接池(%s) 的客户端不能为空!', static::class));
        }

        return $grpcClient->createConnection($this, $this->clientName);
    }

    /**
     * 初始化自定义配置
     *
     * @param $client
     */
    private function initCustomConfig($client)
    {
        $config = $client->getCarGateWayCenter();

        if (empty($config)) {
            return;
        }

        $customConfig = array();
        if (isset($config['options']) && !empty($config['options'])) {
            $customConfig = $config['options'];
        } elseif (!empty($client->getOptions())) {
            $customConfig = $client->getOptions();
        } else {
            return;
        }

        foreach ($customConfig as $field => $value) {
            $this->$field = $value;
        }
    }
}
