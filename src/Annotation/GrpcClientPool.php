<?php declare(strict_types=1);


namespace Hzwz\Grpc\Client\Annotation;


class GrpcClientPool
{
    public const CAR_USER_CENTER    = 'CarUserCenterPool';
    public const CAR_DEVICE_CENTER  = 'CarDeviceCenterPool';
    public const CAR_PAY_CENTER     = 'CarPayCenterPool';
    public const CAR_PUBLIC_CENTER  = 'CarPublicCenterPool';
    public const CAR_GATEWAY_CENTER = 'CarGatewayCenterPool';
    public const CAR_HWB_CENTER     = 'CarHwbCenterPool';
}
