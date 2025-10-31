<?php

declare(strict_types=1);

namespace UserLevelBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use UserLevelBundle\UserLevelBundle;

/**
 * @internal
 */
#[CoversClass(UserLevelBundle::class)]
#[RunTestsInSeparateProcesses]
final class UserLevelBundleTest extends AbstractBundleTestCase
{
}
