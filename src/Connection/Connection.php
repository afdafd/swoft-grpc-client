<?php declare(strict_types=1);

namespace Hzwz\Grpc\Client\Connection;


use Hzwz\Grpc\Client\StatusCode;
use Swoft\Log\Helper\Log;
use Hzwz\Grpc\Client\Exception\GrpcClientException;
use Hzwz\Grpc\Client\Pool\CarUserCenterPool;
use phpDocumentor\Reflection\Project;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Concern\PrototypeTrait;
use Swoft\Connection\Pool\Contract\PoolInterface;
use Swoft\Connection\Pool\Exception\ConnectionPoolException;
use Hzwz\Grpc\Client\Client as GrpcClient;
use Swoole\Coroutine\Http2\Client as SwooleHttp2Client;


/**
 * Class Connection
 * @package Hzwz\Grpc\Client\Connection
 *
 * @Bean()
 */
class Connection extends AbstractConnection
{
    use PrototypeTrait;

    /**
     * @var SwooleHttp2Client
     */
    protected $connection;

    /**
     * @var GrpcClient
     */
    protected $client;

    /**
     * @var
     */
    protected $clientConfigName;

    /**
     * @param GrpcClient $client
     * @param CarUserCenterPool $pool
     * @param string $poolName
     *
     * @return Connection
     */
    public static function new(GrpcClient $client, PoolInterface $pool, string $clientConfigName): Connection
    {
        $instance = self::__instance();

        $instance->pool     = $pool;
        $instance->client   = $client;
        $instance->lastTime = time();
        $instance->clientConfigName = $clientConfigName;
        $instance->id       = $pool->getConnectionId();

        return $instance;
    }

    /**
     * 创建SwooleHttp2Client连接
     *
     * @throws GrpcClientException
     */
    public function create(): void
    {
        try {
            //获取client的配置信息
            $clientConfig = $this->client->{'get' . ucfirst($this->clientConfigName)}();

            //建立连接
            $client = new SwooleHttp2Client($clientConfig['host'], $clientConfig['port'], $clientConfig['ssl'] ?? false);

            //客户端自定义配置设置
            if (isset($clientConfig['setting']) && !empty($clientConfig['setting'])) {
                $client->set($clientConfig['setting']);
            } else {
                $generalConfig = $this->client->getSetting();
                if (!empty($generalConfig)) {
                    $client->set($generalConfig);
                } else {
                    $client->set($this->client->defaultSetting());
                }
            }

            if (!$client->connect()) {
                throw new ConnectionPoolException(
                    sprintf("Grpc连接服务端失败；host=%s；port=%d", $clientConfig['host'], $clientConfig['port']),
                    StatusCode::UNAVAILABLE
                );
            }

            $this->connection = $client;
        } catch (\Throwable $exception) {
            Log::error("GrpcServerLinkError:createError", [
                'errorMsg'          => $exception->getMessage(),
                'errorSite'         => $exception->getFile() .'|'. $exception->getLine(),
                'errorDetails'      => $exception->getTraceAsString(),
                'error_happen_time' => date('Y-m-d H:i:s'),
            ]);

            throw $exception;
        }
    }

    /**
     * 关闭连接
     */
    public function close(): void
    {
        $this->connection->close();
    }

    /**
     * @return bool
     */
    public function reconnect(): bool {}

    /**
     * @return GrpcClient
     */
    public function getClient(): GrpcClient
    {
        return $this->client;
    }

    /**
     * @return SwooleHttp2Client
     * @throws GrpcClientException
     */
    public function getS2Client(): SwooleHttp2Client
    {
        if (!$this->connection) {
            $this->create();
        }

        return $this->connection;
    }

    /**
     * 获取错误code
     *
     * @return int
     */
    public function getErrCode(): int
    {
        return (int)$this->connection->errCode;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getErrMsg(): string
    {
        return (string)$this->connection->errMsg;
    }
}
