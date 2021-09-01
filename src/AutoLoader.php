<?php declare(strict_types=1);


namespace Hzwz\Grpc\Client;


use Hzwz\Grpc\Server\Parser;
use Swoft\Helper\ComposerJSON;
use Swoft\SwoftComponent;

class AutoLoader extends SwoftComponent
{
    public function getPrefixDirs(): array
    {
        return [
            __NAMESPACE__ => __DIR__,
        ];
    }

    /**
     * @return array
     */
    public function metadata(): array
    {
        $jsonFile = dirname(__DIR__) . '/composer.json';

        return ComposerJSON::open($jsonFile)->getMetadata();
    }

    /**
     * @return array
     */
    public function beans(): array
    {
        return [
            'grpcClients'  => [
                'class' => \Hzwz\Grpc\Client\Client::class,
            ],

            'grpcClients.pool'  => [
                'class'  => \Hzwz\Grpc\Client\Pool\Pool::class,
                'client' => bean('grpcClients')
            ],
        ];
    }
}
