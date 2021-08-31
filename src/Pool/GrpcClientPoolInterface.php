<?php declare(strict_types=1);


namespace Hzwz\Grpc\Client\Pool;


use Hzwz\Grpc\Client\Client;

interface GrpcClientPoolInterface
{
    public function baseInit(Client $client);
}
