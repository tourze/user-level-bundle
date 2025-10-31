<?php

declare(strict_types=1);

namespace UserLevelBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use UserLevelBundle\Entity\UpgradeRule;

#[AdminCrud(routePath: '/user-level/upgrade-rule', routeName: 'user_level_upgrade_rule')]
final class UpgradeRuleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UpgradeRule::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID')->onlyOnIndex();
        yield TextField::new('title', '规则名称')
            ->setHelp('升级规则名称，最多100个字符')
            ->setMaxLength(100)
        ;
        yield IntegerField::new('value', '到达数值')
            ->setHelp('达到此数值即可升级，必须大于等于0')
        ;
        yield AssociationField::new('userLevel', '目标等级')
            ->setHelp('达成条件后升级到的等级')
        ;
        yield BooleanField::new('valid', '是否有效')
            ->setHelp('标记该升级规则是否有效')
        ;
        yield TextField::new('createdBy', '创建者')->onlyOnIndex();
        yield TextField::new('updatedBy', '更新者')->onlyOnIndex();
        yield DateTimeField::new('createTime', '创建时间')->onlyOnIndex();
        yield DateTimeField::new('updateTime', '更新时间')->onlyOnIndex();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('title')
            ->add('value')
            ->add('userLevel')
            ->add('valid')
            ->add('createTime')
            ->add('updateTime')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('升级规则')
            ->setEntityLabelInPlural('升级规则列表')
            ->setPageTitle('index', '升级规则管理')
            ->setPageTitle('detail', '规则详情')
            ->setPageTitle('edit', '编辑规则')
            ->setPageTitle('new', '新建规则')
        ;
    }
}
