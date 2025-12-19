<?php

declare(strict_types=1);

namespace UserLevelBundle\Tests\Param;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use UserLevelBundle\Param\GetLevelLogsByBizUserIdParam;

/**
 * @internal
 */
#[CoversClass(GetLevelLogsByBizUserIdParam::class)]
final class GetLevelLogsByBizUserIdParamTest extends TestCase
{
    public function testParamCanBeConstructed(): void
    {
        $param = new GetLevelLogsByBizUserIdParam(
            userId: 'test-user-id',
        );

        $this->assertInstanceOf(RpcParamInterface::class, $param);
        $this->assertSame('test-user-id', $param->userId);
    }

    public function testParamIsReadonly(): void
    {
        $param = new GetLevelLogsByBizUserIdParam(
            userId: 'another-user-id',
        );

        $this->assertSame('another-user-id', $param->userId);
    }
}
