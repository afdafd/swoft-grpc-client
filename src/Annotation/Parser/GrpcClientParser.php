<?php declare(strict_types=1);

namespace Hzwz\Grpc\Client\Annotation\Parser;

use Hzwz\Grpc\Client\Exception\GrpcClientException;
use Hzwz\Grpc\Client\GrpcClientRegister;
use PhpDocReader\AnnotationException;
use PhpDocReader\PhpDocReader;
use ReflectionException;
use Swoft\Annotation\Annotation\Mapping\AnnotationParser;
use Swoft\Annotation\Annotation\Parser\Parser;
use Hzwz\Grpc\Client\Annotation\Mapping\GrpcClient;

/**
 * Class GrpcClientParser
 * @package Annotation\Parser
 *
 * @AnnotationParser(GrpcClient::class)
 */
class GrpcClientParser extends Parser
{
    /**
     * 解析
     *
     * @param int $type
     * @param object $annotationObj
     *
     * @return array
     *
     * @throws GrpcClientException
     * @throws AnnotationException
     * @throws ReflectionException
     */
    public function parse(int $type, $annotationObj): array
    {
        //解析@grpcClient 和 @var
        $phpReader       = new PhpDocReader();
        $reflectProperty = new \ReflectionProperty($this->className, $this->propertyName);
        $propClassType   = $phpReader->getPropertyClass($reflectProperty);

        if (empty($propClassType)) {
            throw new GrpcClientException(sprintf(
                '`@GrpcClient`(%s->%s) 必须定义 `@var xxx`', $this->className, $this->propertyName));
        }

        $this->definitions[$this->className] = ['class' => $this->className];
        GrpcClientRegister::register($propClassType, $annotationObj->getPool());

        return [$this->className, false];
    }
}
