<?php

declare(strict_types=1);

namespace UserLevelBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use UserLevelBundle\Entity\UpgradeProgress;

#[AdminCrud(routePath: '/user-level/upgrade-progress', routeName: 'user_level_upgrade_progress')]
final class UpgradeProgressCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UpgradeProgress::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID')->onlyOnIndex();
        yield AssociationField::new('user', '用户')
            ->setHelp('关联的用户')
        ;
        yield AssociationField::new('upgradeRule', '升级规则')
            ->setHelp('应用的升级规则')
        ;
        yield IntegerField::new('value', '当前进度')
            ->setHelp('用户当前的升级进度值')
            ->setFormTypeOption('attr', ['min' => 0])
        ;
        yield DateTimeField::new('createTime', '创建时间')->onlyOnIndex();
        yield DateTimeField::new('updateTime', '更新时间')->onlyOnIndex();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('user')
            ->add('upgradeRule')
            ->add('value')
            ->add('createTime')
            ->add('updateTime')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('升级进度')
            ->setEntityLabelInPlural('升级进度列表')
            ->setPageTitle('index', '升级进度管理')
            ->setPageTitle('detail', '进度详情')
            ->setPageTitle('edit', '编辑进度')
            ->setPageTitle('new', '新建进度')
        ;
    }
}
