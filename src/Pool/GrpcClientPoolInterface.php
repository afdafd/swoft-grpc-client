<?php declare(strict_types=1);


namespace Hzwz\Grpc\Client\Pool;


use Hzwz\Grpc\Client\Client as GrpcClient;

interface GrpcClientPoolInterface
{
    public function baseInit(GrpcClient $client);
}
