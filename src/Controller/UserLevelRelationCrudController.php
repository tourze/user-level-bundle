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
use UserLevelBundle\Entity\UserLevelRelation;

#[AdminCrud(routePath: '/user-level/user-level-relation', routeName: 'user_level_user_level_relation')]
final class UserLevelRelationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserLevelRelation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID')->onlyOnIndex();
        yield AssociationField::new('user', '用户')
            ->setHelp('关联的用户')
        ;
        yield AssociationField::new('level', '用户等级')
            ->setHelp('用户当前的等级')
        ;
        yield BooleanField::new('valid', '是否有效')
            ->setHelp('标记该用户等级关系是否有效')
        ;
        yield DateTimeField::new('createTime', '创建时间')->onlyOnIndex();
        yield DateTimeField::new('updateTime', '更新时间')->onlyOnIndex();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('user')
            ->add('level')
            ->add('valid')
            ->add('createTime')
            ->add('updateTime')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('用户等级关系')
            ->setEntityLabelInPlural('用户等级关系列表')
            ->setPageTitle('index', '用户等级关系管理')
            ->setPageTitle('detail', '关系详情')
            ->setPageTitle('edit', '编辑关系')
            ->setPageTitle('new', '新建关系')
        ;
    }
}
