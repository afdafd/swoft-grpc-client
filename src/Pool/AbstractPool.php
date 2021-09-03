<?php declare(strict_types=1);

namespace Hzwz\Grpc\Client\Pool;

use Swoft\Connection\Pool\Contract\ConnectionInterface;
use Swoft\Connection\Pool\Contract\PoolInterface;
use Swoft\Connection\Pool\Exception\ConnectionPoolException;
use Swoft\Log\Helper\CLog;
use Swoft\Log\Helper\Log;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use \Throwable;

/**
 * Class AbstractPool
 * @package AbstractPool
 */
abstract class AbstractPool implements PoolInterface
{
    /**
     * 最小连接数
     *
     * @var int
     */
    protected $minActive = 5;

    /**
     * 最大连接数
     *
     * @var int
     */
    protected $maxActive = 10;

    /**
     * 最大等待连接数，如果为0表示没有限制
     *
     * @var int
     */
    protected $maxWait = 0;

    /**
     *最大等待时间(秒)，如果为0表示没有限制
     *
     * @var float
     */
    protected $maxWaitTime = 0;

    /**
     * 最大空闲时间（秒）
     *
     * @var int
     */
    protected $maxIdleTime = 60;

    /**
     * 最大等待关闭时间
     *
     * @var float
     */
    protected $maxCloseTime = 3;

    /**
     * @var bool
     */
    protected $init = false;

    /**
     * @var string[]
     *
     * @example [
     *    'carDeviceCenter'  => channel,
     *    'carGatewayCenter' => channel,
     *    'carHwbCenter'     => channel,
     *    'carPayCenter'     => channel,
     *    'carPublicCenter'  => channel,
     *    'carUserCenter'    => channel,
     * ]
     */
    protected $channel = [
      'carDeviceCenter'  => null,
      'carGatewayCenter' => null,
      'carHwbCenter'     => null,
      'carPayCenter'     => null,
      'carPublicCenter'  => null,
      'carUserCenter'    => null,
  ];

    /**
     * 当前连接数
     *
     * @var int
     */
    protected $count = 0;

    /**
     * 下一个连接ID
     *
     * @var int
     */
    protected $connectionId = 0;

    /**
     * 初始化连接池
     *
     * @throws ConnectionPoolException
     */
    public function initPool(): void
    {
        if (!$this->init) {
            return;
        }

        // 允许初始化连接池
        for ($i = 0; $i < $this->minActive; $i++) {
            $connection = $this->getConnection();
            $connection->setRelease(true);
            $connection->release();
        }

        CLog::info('Initialize pool ' . static::class . ' pool size=' . $this->count);
    }

    /**
     * @return int
     */
    public function getConnectionId(): int
    {
        $this->connectionId++;
        return $this->connectionId;
    }

    /**
     * 获取连接。首先从管道里获取。如果管道里不存在就新创建一个连接返回
     *
     * @return ConnectionInterface
     * @throws ConnectionPoolException
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->getConnectionByChannel();
    }

    /**
     * 是否连接。把连接放回到 channel 里
     *
     * @param ConnectionInterface $connection
     */
    public function release(ConnectionInterface $connection): void
    {
        $this->releaseToChannel($connection);
    }

    /**
     * 删除连接数。当连接发生错误时执行删除
     */
    public function remove(): void
    {
        $this->count--;
    }

    /**
     * 关闭连接
     *
     * @return int
     */
    public function close(): int
    {
        $i = 0;
        if ($this->channel[$this->clientName] === null) {
            return $i;
        }

        for (; $i < $this->count; $i++) {
            $connection = $this->channel[$this->clientName]->pop($this->maxCloseTime);
            if ($connection === false) {
                break;
            }

            if (!$connection instanceof ConnectionInterface) {
                continue;
            }

            try {
                // May be disconnected
                $connection->close();
            } catch (Throwable $e) {
                CLog::warning('连接池关闭指定连接发生错误： ' . $e->getMessage());
            }
        }

        return $this->count;
    }

    /**
     * 从channel里获取Connect连接
     *
     * @return ConnectionInterface
     * @throws ConnectionPoolException
     */
    private function getConnectionByChannel(): ConnectionInterface
    {
        if ($this->channel[$this->clientName] === null) {
            $this->channel[$this->clientName] = new Channel($this->maxActive);
        }

        // 如果连接数小于设置的最小连接数，创建新连接
        if ($this->count < $this->minActive) {
            return $this->create();
        }

        // 如果管道不为空，尝试抛出一个连接
        $connection = null;
        if (!$this->channel[$this->clientName]->isEmpty()) {
            $connection = $this->popByChannel();
        }

        // 如果从管道里获取到了有效的连接，更新连接的最后活跃时间
        if ($connection !== null) {
            $connection->updateLastTime();
            return $connection;
        }

        // 当connection为空且连接数没有达到设置的最大连接数时，创建新连接
        if ($this->count < $this->maxActive) {
            return $this->create();
        }

        // 如果设置了最大等待连接数，且消费者数大于/等于了最大等待数，提示错误
        $stats = $this->channel[$this->clientName]->stats();
        if ($this->maxWait > 0 && $stats['consumer_num'] >= $this->maxWait) {
            throw new ConnectionPoolException(sprintf(
                'Channel consumer_num 已满, maxActive=%d, maxWait=%d, currentCount=%d',
                $this->maxActive,
                $this->maxWaitTime,
                $this->count
            ));
        }

        /* @var ConnectionInterface $connection */
        //协程休眠并在所设置的 'maxWaitTime' 后恢复协程，如果为false表示等待超时
        $connection = $this->channel[$this->clientName]->pop($this->maxWaitTime);
        if ($connection === false) {
            throw new ConnectionPoolException(
                sprintf('Channel pop 超时 %fs', $this->maxWaitTime)
            );
        }

        $connection->updateLastTime();
        return $connection;
    }

    /**
     * 创建连接并且返回
     *
     * @return ConnectionInterface
     * @throws ConnectionPoolException
     */
    private function create(): ConnectionInterface
    {
        try {
            $connection = $this->createConnection();
            $this->count++;

        } catch (Throwable $e) {
            $this->count--;

            throw new ConnectionPoolException(sprintf(
                '创建连接数失败(%s) file(%s) line (%d)', $e->getMessage(), $e->getFile(), $e->getLine()
            ));
        }

        return $connection;
    }

    /**
     * 从管道里获取连接
     *
     * @return ConnectionInterface|null
     */
    private function popByChannel(): ?ConnectionInterface
    {
        $time = time();

        while (!$this->channel[$this->clientName]->isEmpty()) {
            /* @var ConnectionInterface $connection */
            $connection = $this->channel[$this->clientName]->pop();
            $lastTime   = $connection->getLastTime();

            // 判断连接的空闲时间是否大于了设置的最大空闲时间
            if ($time - $lastTime > $this->maxIdleTime) {
                try {
                    $connection->close();
                } catch (Throwable $e) {
                    Log::error("popByChannelError: 关闭连接失败", [
                        'errorMsg' => $e->getMessage(),
                        'waitCloseConnect' => $connection,
                        'channelDetail' => $this->channel[$this->clientName]->stats(),
                        'closeErrorTime' => date('Y-m-d H:i:s'),
                        'settingMaxIdleTime' => $this->maxIdleTime,
                        'connectLastActiveTime' => $lastTime
                    ]);
                }

                $this->count--;
                continue;
            }

            return $connection;
        }

        return null;
    }

    /**
     * 归还连接到池里
     *
     * @param ConnectionInterface $connection
     */
    private function releaseToChannel(ConnectionInterface $connection): void
    {
        $stats = $this->channel[$this->clientName]->stats();
        if ($stats['queue_num'] < $this->maxActive) {
            $this->channel[$this->clientName]->push($connection);
        }
    }
}
