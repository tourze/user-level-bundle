# User Level Bundle 用户等级模块

[![PHP 版本](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://www.php.net/)
[![许可证](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![构建状态](https://img.shields.io/badge/build-passing-brightgreen.svg)](https://github.com/tourze/php-monorepo)
[![代码覆盖率](https://img.shields.io/badge/coverage-90%25-brightgreen.svg)](https://github.com/tourze/php-monorepo)

[English](README.md) | [中文](README.zh-CN.md)

用于管理用户等级、升级规则和等级进度跟踪的 Symfony 扩展包。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [配置](#配置)
- [依赖关系](#依赖关系)
- [使用方法](#使用方法)
- [高级用法](#高级用法)
- [API 参考](#api-参考)
- [测试](#测试)
- [贡献](#贡献)
- [许可证](#许可证)

## 功能特性

- **用户等级管理**: 创建和管理不同的用户等级
- **升级规则**: 使用可自定义条件定义等级进度规则
- **进度跟踪**: 通过详细分析监控用户升级进度
- **管理界面**: 用于等级管理的 JSON-RPC 程序接口
- **Doctrine 集成**: 完整的 ORM 支持和优化的存储库
- **验证功能**: 使用 Symfony 约束进行全面的实体验证
- **可扩展性**: 易于扩展自定义升级逻辑和规则

## 安装

使用 Composer 安装扩展包：

```bash
composer require tourze/user-level-bundle
```

在 `config/bundles.php` 中启用扩展包：

```php
return [
    // ...
    UserLevelBundle\UserLevelBundle::class => ['all' => true],
];
```

更新数据库结构：

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## 配置

扩展包开箱即用，只需最少配置。服务配置会自动加载。

### 可选配置

你可以在 `config/packages/user_level.yaml` 中自定义扩展包行为：

```yaml
# config/packages/user_level.yaml
user_level:
    # 根据需要在此处添加配置选项
```

## 使用方法

### 基本使用

1. **创建用户等级**:

```php
use UserLevelBundle\Entity\Level;

$level = new Level();
$level->setTitle('青铜');
$level->setLevel(1);
$level->setValid(true);
$entityManager->persist($level);
$entityManager->flush();
```

2. **定义升级规则**:

```php
use UserLevelBundle\Entity\UpgradeRule;

$rule = new UpgradeRule();
$rule->setTitle('积分升青铜');
$rule->setValue(100);
$rule->setLevel($level);
$rule->setValid(true);
$entityManager->persist($rule);
$entityManager->flush();
```

3. **使用用户等级服务**:

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

### 使用存储库

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

## 高级用法

### 自定义升级逻辑

扩展 `UserLevelUpgradeService` 来实现自定义升级逻辑：

```php
use UserLevelBundle\Service\UserLevelUpgradeService;

class CustomUpgradeService extends UserLevelUpgradeService
{
    public function checkCustomUpgradeConditions(UserInterface $user): bool
    {
        // 在此处实现你的自定义逻辑
        return true;
    }
}
```

### 管理程序

扩展包提供用于管理的 JSON-RPC 程序：

- `AdminCreateLevel`: 创建新的用户等级
- `AdminUpdateLevel`: 更新现有等级
- `AdminDeleteLevel`: 删除等级
- `AdminGetLevelList`: 获取分页等级列表
- `GetLevelLogsByBizUserId`: 获取用户等级分配日志

### 数据库架构

扩展包创建以下数据表：

- `biz_user_level`: 用户等级配置
- `biz_user_level_relation`: 用户-等级关系
- `biz_user_level_upgrade_rule`: 升级规则
- `biz_user_level_upgrade_progress`: 用户进度跟踪
- `user_level_assign_log`: 等级分配历史

## API 参考

### 实体类

- **Level**: 表示具有标题和数值的用户等级
- **UserLevelRelation**: 将用户链接到其当前等级
- **UpgradeRule**: 定义等级进度的条件
- **UpgradeProgress**: 跟踪用户向下一等级的进度
- **AssignLog**: 记录等级分配历史

### 服务

- **UserLevelUpgradeService**: 处理等级升级的核心服务

### 存储库

所有实体都有对应的存储库，提供标准 CRUD 操作和自定义查询方法。

## 测试

运行测试套件：

```bash
./vendor/bin/phpunit packages/user-level-bundle/tests
```

运行 PHPStan 分析：

```bash
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/user-level-bundle
```

## 贡献

1. Fork 仓库
2. 创建你的功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交你的更改 (`git commit -m 'Add some amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 打开一个 Pull Request

请确保所有测试通过并遵循现有的代码风格。

## 依赖关系

此扩展包需要：

### 系统要求
- PHP 8.1 或更高版本
- Symfony 6.4+
- Doctrine ORM 3.0+

### 核心依赖
- `doctrine/collections`: ^2.3
- `doctrine/dbal`: ^4.0
- `doctrine/doctrine-bundle`: ^2.13
- `doctrine/orm`: ^3.0
- `symfony/config`: ^6.4
- `symfony/dependency-injection`: ^6.4
- `symfony/http-kernel`: ^6.4
- `symfony/security-core`: ^6.4
- `symfony/serializer`: ^6.4

### Tourze 扩展包依赖
- `tourze/arrayable`: 0.0.*
- `tourze/bundle-dependency`: 0.0.*
- `tourze/doctrine-snowflake-bundle`: 0.1.*
- `tourze/doctrine-timestamp-bundle`: 0.0.*
- `tourze/doctrine-track-bundle`: 0.1.*
- `tourze/doctrine-user-bundle`: 0.0.*
- `tourze/json-rpc-core`: 0.0.*
- `tourze/json-rpc-log-bundle`: 0.1.*
- `tourze/json-rpc-paginator-bundle`: 0.0.*

完整的依赖项列表请查看 [composer.json](composer.json)。

## 许可证

本扩展包在 MIT 许可证下发布。更多信息请参阅 [LICENSE](LICENSE) 文件。