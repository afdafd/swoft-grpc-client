<?php declare(strict_types=1);

namespace Hzwz\Grpc\Client;

use Hzwz\Grpc\Client\Exception\GrpcClientException;

/**
 * Class ReferenceRegister
 * @package GrpcClientRegister
 */
class GrpcClientRegister
{
    /**
     * @var array
     *
     * 实例
     * ['className' => 'poolName']
     */
    public static $grpcClients = [];

    /**
     * 注册类和池的关联关系
     *
     * @param string $className
     * @param string $pool
     */
    public static function register(string $className, string $pool): void
    {
        self::$grpcClients[$className]['pool'] = $pool;
    }

    /**
     * @param string $className
     * @return string
     * @throws GrpcClientException
     */
    public static function getPool(string $className): string
    {
        $pool = self::$grpcClients[$className]['pool'] ?? '';
        if (empty($pool)) {
            throw new GrpcClientException(sprintf('`@GrpcClient` pool (%s) 不存在!', $className));
        }

        return $pool;
    }
}
