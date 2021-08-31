<?php declare(strict_types=1);

namespace  Hzwz\Grpc\Client;

use Throwable;
use Swoole\Coroutine;
use Swoft\Log\Helper\Log;
use Swoft\Bean\BeanFactory;
use Hzwz\Grpc\Server\Parser;
use Swoole\Coroutine\Channel;
use Hzwz\Grpc\Client\Pool\AbstractPool;
use Google\Protobuf\Internal\Message;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Hzwz\Grpc\Client\Connection\Connection;
use Hzwz\Grpc\Client\Pool\CarUserCenterPool;
use Hzwz\Grpc\Client\Exception\GrpcClientException;
use Swoft\Connection\Pool\Contract\PoolInterface;
use Hzwz\Grpc\Client\Exception\InvalidArgumentException;
use Swoole\Coroutine\Http2\Client as SwooleHttp2Client;

/**
 * Class Client
 * @package GrpcClient
 *
 * @Bean()
 */
class Client
{
    /**
     * 默认超时时间（单位/秒）
     */
    const GRPC_DEFAULT_TIMEOUT = 10.0;

    /**
     * @var string
     */
    public $callClassName = '';

    /**
     * 汽车充电桩用户中心
     * @var array
     */
    protected $car_user_center    = [];

    /**
     * 汽车充电桩设备中心
     * @var array
     */
    protected $car_device_center  = [];

    /**
     * 汽车充电桩支付中心
     * @var array
     */
    protected $car_pay_center     = [];

    /**
     * 汽车充电桩公共服务中心
     * @var array
     */
    protected $car_public_center  = [];

    /**
     * 汽车充电桩网关中心
     * @var array
     */
    protected $car_gateway_center = [];

    /**
     * 汽车充电桩硬件业务中心 (hardwares business)
     * @var array
     */
    protected $car_hwb_center     = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $setting = [];

    /**
     * 客户端的主协程ID
     *
     * @var int
     */
    protected $mainCoroutineId = 0;

    /**
     * @var int
     */
    protected $recvCoroutineId = 0;

    /**
     * @var int
     */
    protected $sendCoroutineId = 0;

    /**
     * 获取当前发送流id(作为ret val)的通道。
     *
     * @Inject()
     * @var ChannelPool
     */
    protected $resultChannel;

    /**
     * @var SwooleHttp2Client
     */
    protected $swooleHttp2Client;

    /**
     * @var string
     */
    protected $clientName = '';

    /**
     * @var array
     */
    protected $channelMap = [];


    public function __construct(string $callClassName = '')
    {
        $this->callClassName = $callClassName;
    }

    /**
     * 创建连接
     *
     * @param $pool
     * @param string $poolName
     * @return Connection
     * @throws GrpcClientException
     */
    public function createConnection(PoolInterface $pool, string $clientName = ''): Connection
    {
        $connection = Connection::new($this, $pool, $clientName);
        $connection->create();

        return $connection;
    }

    /**
     * 获取汽车桩用户中心设置
     * @return array
     */
    public function getCarUserCenter(): array
    {
        return $this->car_user_center;
    }

    /**
     * 获取汽车桩设备中心设置
     * @return array
     */
    public function getCarDeviceCenter(): array
    {
        return $this->car_device_center;
    }

    /**
     * 获取汽车桩支付中心设置
     * @return array
     */
    public function getCarPayCenter(): array
    {
        return $this->car_pay_center;
    }

    /**
     * 获取汽车桩公共服务中心设置
     * @return array
     */
    public function getCarPublicCenter(): array
    {
        return $this->car_public_center;
    }

    /**
     * 获取汽车桩网关中心设置
     * @return array
     */
    public function getCarGateWayCenter(): array
    {
        return $this->car_gateway_center;
    }

    /**
     * 获取汽车桩硬件业务中心设置
     * @return array
     */
    public function getCarHwbCenter(): array
    {
        return $this->car_hwb_center;
    }

    /**
     * @return array
     */
    public function getSetting(): array
    {
        return $this->setting;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * 从对应客户端的连接池里获取一个连接
     *
     * @return Connection
     * @throws GrpcClientException
     */
    public function geClientConnection(): Connection
    {
        $poolName = GrpcClientRegister::getPool($this->callClassName);
        $pool = BeanFactory::getBean($poolName);

        /**
         * @var Connection $connection
         * @var AbstractPool $pool
         */
        $connection = $pool->getConnection();
        $connection->setRelease(true);

        return $connection;
    }

    /**
     * 默认的配置
     *
     * @return array
     */
    public function defaultSetting(): array
    {
        return [
            'timeout'       => self::GRPC_DEFAULT_TIMEOUT,
            'send_yield'    => false,
            'ssl'           => false,
            'ssl_host_name' => false,
            'credentials'   => null,
        ];
    }

    /**
     * 发起请求
     *
     * @param string $method
     * @param Message $argument
     * @param $deserialize
     * @param array $metadata
     * @param array $options
     * @return array
     * @throws GrpcClientException
     * @throws Throwable
     */
    protected function _simpleRequest(string $method, Message $argument, $deserialize, array $metadata = [], array $options = []): array
    {
        $channel = $this->resultChannel->get();
        $options['headers'] = ($options['headers'] ?? []) + $metadata;

        $response = retry($options['retry_num'] ?? 3, function() use($method, $argument, $options, $channel) {
            try {
                $connect = $this->geClientConnection();
                $swooleHttp2Client = $connect->getS2Client();
                Coroutine::create(function() use($method, $argument, $options, $swooleHttp2Client, $channel) {
                    $swooleHttp2Client->send($this->buildRequest($method, $argument, $options));

                    $response = $swooleHttp2Client->recv();
                    if ($response === false) {
                        throw new GrpcClientException('接收Grpc服务端消息失败' .
                            $swooleHttp2Client->errMsg,
                            $swooleHttp2Client->errCode
                        );
                    }

                    $channel->push($response);
                });

                $response = $channel->pop(self::GRPC_DEFAULT_TIMEOUT);
                if ($response === false || $channel->errCode === SWOOLE_CHANNEL_TIMEOUT) {
                    throw new GrpcClientException('接收Grpc服务端消息超时' .
                        $swooleHttp2Client->errMsg,
                        $swooleHttp2Client->errCode
                    );
                }

                return $response;
            } catch (Throwable $exception) {
                Log::error("grpcClientSendDataError: ", [
                    'errorMsg'     => $exception->getMessage(),
                    'errorSite'    => $exception->getFile() .'|'. $exception->getLine(),
                    'errorDetails' => $exception->getTraceAsString(),
                ]);

                throw $exception;
            } finally {
                $channel->close();
                if (isset($connect) && $connect instanceof Connection) {
                    $connect->release();
                }
            }
        }, $options['retry_sleep'] ?? 100);

        return Parser::parseResponse($response, $deserialize);
    }

    /**
     * 构建请求
     *
     * @param string $method
     * @param Message $argument
     * @param array $options
     * @return Request
     */
    private function buildRequest(string $method, Message $argument, array $options): Request
    {
        $headers = $options['headers'] ?? [];
        return new Request($method, $argument, $headers);
    }
}
