<?php declare(strict_types=1);


namespace Hzwz\Grpc\Client\Utils;


class Backoff
{
    /**
     * 最大补偿时间间隔.
     */
    private const CAP = 60 * 1000; // 一分钟

    /**
     * 第一次补偿时间间隔
     * @var int
     */
    private $firstMs;

    /**
     * 当前补偿时间间隔.
     * @var int
     */
    private $currentMs;

    /**
     * Backoff constructor.
     * @param int $firstMs  第一次时间补偿以毫秒计
     */
    public function __construct(int $firstMs = 0)
    {
        if ($firstMs < 0) {
            throw new \InvalidArgumentException(
                '第一次补偿间隔必须大于或等于0'
            );
        }

        if ($firstMs > Backoff::CAP) {
            throw new \InvalidArgumentException(
                sprintf(
                    '第一次补偿间隔必须小于或等于 %d 毫秒',
                    self::CAP
                )
            );
        }

        $this->firstMs = $firstMs;
        $this->currentMs = $firstMs;
    }

    /**
     * 休眠到下一次执行
     */
    public function sleep(): void
    {
        if ($this->currentMs === 0) {
            return;
        }

        usleep($this->currentMs * 1000);

        //使用解除相关抖动
        //参考: https://aws.amazon.com/blogs/architecture/exponential-backoff-and-jitter/
        $this->currentMs = rand($this->firstMs, $this->currentMs * 3);

        if ($this->currentMs > self::CAP) {
            $this->currentMs = self::CAP;
        }
    }

    /**
     * 获取下一个时间补偿值
     * @return int next backoff
     */
    public function nextBackoff(): int
    {
        return $this->currentMs;
    }
}
