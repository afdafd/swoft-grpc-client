<?php declare(strict_types=1);

use Hzwz\Grpc\Client\Utils\Backoff;

if (!function_exists('retry')) {
    /**
     * 重试操作
     *
     * @param float|int $times
     * @param int $sleep millisecond
     * @throws \Throwable
     */
    function retry($times, callable $callback, int $sleep = 0)
    {
        $backoff = new Backoff($sleep);

        beginning:
        try {
            return $callback();
        } catch (\Throwable $e) {
            if (--$times < 0) {
                throw $e;
            }
            $backoff->sleep();
            goto beginning;
        }
    }
}
