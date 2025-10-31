<?php

declare(strict_types=1);

namespace UserLevelBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use UserLevelBundle\Entity\Level;

#[AdminCrud(routePath: '/user-level/level', routeName: 'user_level_level')]
final class LevelCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Level::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID')->onlyOnIndex();
        yield TextField::new('title', '等级名称')
            ->setHelp('等级名称，最多100个字符')
            ->setMaxLength(100)
        ;
        yield IntegerField::new('level', '等级值')
            ->setHelp('等级数值，必须大于等于0且唯一')
        ;
        yield BooleanField::new('valid', '是否有效')
            ->setHelp('标记该等级是否有效')
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
            ->add('level')
            ->add('valid')
            ->add('createTime')
            ->add('updateTime')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('等级')
            ->setEntityLabelInPlural('等级列表')
            ->setPageTitle('index', '等级管理')
            ->setPageTitle('detail', '等级详情')
            ->setPageTitle('edit', '编辑等级')
            ->setPageTitle('new', '新建等级')
        ;
    }
}
