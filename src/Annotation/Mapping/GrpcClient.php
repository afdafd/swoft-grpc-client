<?php declare(strict_types=1);

namespace Hzwz\Grpc\Client\Annotation\Mapping;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Class GrpcClientPool
 * @package Hzwz\Grpc\Client\Annotation\Mapping\Annotation\Mapping
 *
 * @Annotation
 * @Target({"PROPERTY"})
 * @Attributes({
 *     @Attribute("event", type="string"),
 * })
 */
class GrpcClient
{
    /**
     * @var string
     */
    private $pool = '';

    /**
     * @var string
     */
    private $requestMethod = 'POST';

    /**
     * Reference constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->pool = $values['value'];
        } elseif (isset($values['pool'])) {
            $this->pool = $values['pool'];
        }

        if (isset($values['requestMethod'])) {
            $this->requestMethod = $values['requestMethod'];
        }
    }

    /**
     * @return string
     */
    public function getPool(): string
    {
        return $this->pool;
    }

    /**
     * @return string
     */
    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }
}
