# User Level Bundle

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)](https://github.com/tourze/php-monorepo)
[![Code Coverage](https://img.shields.io/badge/coverage-90%25-brightgreen.svg)](https://github.com/tourze/php-monorepo)

[English](README.md) | [中文](README.zh-CN.md)

A Symfony bundle for managing user levels, upgrade rules, and level progression tracking.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Dependencies](#dependencies)
- [Usage](#usage)
- [Advanced Usage](#advanced-usage)
- [API Reference](#api-reference)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Features

- **User Level Management**: Create and manage different user levels
- **Upgrade Rules**: Define rules for level progression with customizable criteria
- **Progress Tracking**: Monitor user upgrade progress with detailed analytics
- **Admin Interface**: JSON-RPC procedures for level administration and management
- **Doctrine Integration**: Full ORM support with optimized repositories
- **Validation**: Comprehensive entity validation using Symfony constraints
- **Extensible**: Easy to extend with custom upgrade logic and rules

## Installation

Install the bundle using Composer:

```bash
composer require tourze/user-level-bundle
```

Enable the bundle in your `config/bundles.php`:

```php
return [
    // ...
    UserLevelBundle\UserLevelBundle::class => ['all' => true],
];
```

Update your database schema:

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## Configuration

The bundle works out of the box with minimal configuration. Service configuration is automatically loaded.

### Optional Configuration

You can customize the bundle behavior in your `config/packages/user_level.yaml`:

```yaml
# config/packages/user_level.yaml
user_level:
    # Configuration options will be added here as needed
```

## Usage

### Basic Usage

1. **Create User Levels**:

```php
use UserLevelBundle\Entity\Level;

$level = new Level();
$level->setTitle('Bronze');
$level->setLevel(1);
$level->setValid(true);
$entityManager->persist($level);
$entityManager->flush();
```

2. **Define Upgrade Rules**:

```php
use UserLevelBundle\Entity\UpgradeRule;

$rule = new UpgradeRule();
$rule->setTitle('Points to Bronze');
$rule->setValue(100);
$rule->setLevel($level);
$rule->setValid(true);
$entityManager->persist($rule);
$entityManager->flush();
```

3. **Use the User Level Service**:

```php
use UserLevelBundle\Service\UserLevelUpgradeService;

class UserController
{
    public function __construct(
        private readonly UserLevelUpgradeService $userLevelUpgradeService
    ) {
    }

    public function upgradeUser(UserInterface $user): void
    {
        $this->userLevelUpgradeService->upgrade($user);
    }
}
```

### Working with Repositories

```php
use UserLevelBundle\Repository\LevelRepository;
use UserLevelBundle\Repository\UserLevelRelationRepository;

class UserLevelController
{
    public function __construct(
        private readonly LevelRepository $levelRepository,
        private readonly UserLevelRelationRepository $relationRepository
    ) {
    }

    public function getUserLevel(UserInterface $user): ?Level
    {
        $relation = $this->relationRepository->findOneBy(['user' => $user, 'valid' => true]);
        return $relation?->getLevel();
    }
}
```

## Advanced Usage

### Custom Upgrade Logic

Extend the `UserLevelUpgradeService` to implement custom upgrade logic:

```php
use UserLevelBundle\Service\UserLevelUpgradeService;

class CustomUpgradeService extends UserLevelUpgradeService
{
    public function checkCustomUpgradeConditions(UserInterface $user): bool
    {
        // Implement your custom logic here
        return true;
    }
}
```

### Admin Procedures

The bundle provides JSON-RPC procedures for administration:

- `AdminCreateLevel`: Create new user levels
- `AdminUpdateLevel`: Update existing levels
- `AdminDeleteLevel`: Delete levels
- `AdminGetLevelList`: Get paginated list of levels
- `GetLevelLogsByBizUserId`: Get user level assignment logs

### Database Schema

The bundle creates the following tables:

- `biz_user_level`: User levels configuration
- `biz_user_level_relation`: User-level relationships
- `biz_user_level_upgrade_rule`: Upgrade rules
- `biz_user_level_upgrade_progress`: User progress tracking
- `user_level_assign_log`: Level assignment history

## API Reference

### Entities

- **Level**: Represents a user level with title and numeric value
- **UserLevelRelation**: Links users to their current levels
- **UpgradeRule**: Defines criteria for level progression
- **UpgradeProgress**: Tracks user progress towards next level
- **AssignLog**: Records level assignment history

### Services

- **UserLevelUpgradeService**: Core service for handling level upgrades

### Repositories

All entities have corresponding repositories with standard CRUD operations and custom query methods.

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit packages/user-level-bundle/tests
```

Run PHPStan analysis:

```bash
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/user-level-bundle
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please ensure all tests pass and follow the existing code style.

## Dependencies

This bundle requires:

### System Requirements
- PHP 8.1 or higher
- Symfony 6.4+
- Doctrine ORM 3.0+

### Core Dependencies
- `doctrine/collections`: ^2.3
- `doctrine/dbal`: ^4.0
- `doctrine/doctrine-bundle`: ^2.13
- `doctrine/orm`: ^3.0
- `symfony/config`: ^6.4
- `symfony/dependency-injection`: ^6.4
- `symfony/http-kernel`: ^6.4
- `symfony/security-core`: ^6.4
- `symfony/serializer`: ^6.4

### Tourze Bundle Dependencies
- `tourze/arrayable`: 0.0.*
- `tourze/bundle-dependency`: 0.0.*
- `tourze/doctrine-snowflake-bundle`: 0.1.*
- `tourze/doctrine-timestamp-bundle`: 0.0.*
- `tourze/doctrine-track-bundle`: 0.1.*
- `tourze/doctrine-user-bundle`: 0.0.*
- `tourze/json-rpc-core`: 0.0.*
- `tourze/json-rpc-log-bundle`: 0.1.*
- `tourze/json-rpc-paginator-bundle`: 0.0.*

For a complete list of dependencies, see [composer.json](composer.json).

## License

This bundle is released under the MIT license. See the [LICENSE](LICENSE) file for more information.