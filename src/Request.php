<?php declare(strict_types=1);

namespace Hzwz\Grpc\Client;

use Google\Protobuf\Internal\Message;
use Hzwz\Grpc\Server\Parser;
use Swoole\Http2\Request as SwooleHttp2Request;

class Request extends SwooleHttp2Request
{
    private const DEFAULT_CONTENT_TYPE = 'application/grpc+proto';


    /**
     * 初始化基本的请求数据信息
     *
     * Request constructor.
     * @param string $path
     * @param Message|null $argument
     * @param array $headers
     */
    public function __construct(string $path, Message $argument = null, array $headers = [])
    {
        $this->method = 'POST';
        $this->headers = array_replace($this->getDefaultHeaders(), $headers);
        $this->path = $path;
        $argument && $this->data = Parser::serializeMessage($argument);
    }

    /**
     * 获取默认的header头数据
     *
     * @return array
     */
    public function getDefaultHeaders(): array
    {
        return [
            'te'           => 'trailers',
            'content-type' => self::DEFAULT_CONTENT_TYPE,
            'user-agent'   => $this->buildDefaultUserAgent(),
        ];
    }

    /**
     * 构建默认的用户代理
     *
     * @return string
     */
    private function buildDefaultUserAgent(): string
    {
        return sprintf('Hzwz gRPC/PHP-%s/Swoole-%s', PHP_VERSION, SWOOLE_VERSION);
    }
}
