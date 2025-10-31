<?php

namespace UserLevelBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class UserLevelExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
