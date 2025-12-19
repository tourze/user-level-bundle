<?php

namespace UserLevelBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\EasyAdminMenuBundle\EasyAdminMenuBundle;
use Tourze\JsonRPCPaginatorBundle\JsonRPCPaginatorBundle;
use Tourze\JsonRPCSecurityBundle\JsonRPCSecurityBundle;

class UserLevelBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            JsonRPCPaginatorBundle::class => ['all' => true],
            JsonRPCSecurityBundle::class => ['all' => true],
            EasyAdminMenuBundle::class => ['all' => true],
        ];
    }
}
