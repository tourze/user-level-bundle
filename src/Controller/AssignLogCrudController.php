<?php

declare(strict_types=1);

namespace UserLevelBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use UserLevelBundle\Entity\AssignLog;

#[AdminCrud(routePath: '/user-level/assign-log', routeName: 'user_level_assign_log')]
final class AssignLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AssignLog::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID')->onlyOnIndex();
        yield AssociationField::new('user', '用户')
            ->setHelp('关联的用户')
        ;
        yield AssociationField::new('oldLevel', '原等级')
            ->setHelp('变更前的等级')
        ;
        yield AssociationField::new('newLevel', '新等级')
            ->setHelp('变更后的等级')
        ;
        yield ChoiceField::new('type', '类型')
            ->setChoices([
                '降级' => 0,
                '升级' => 1,
            ])
            ->setHelp('0表示降级，1表示升级')
            ->renderExpanded()
        ;
        yield DateTimeField::new('assignTime', '分配时间')
            ->setHelp('等级分配的时间')
        ;
        yield TextField::new('remark', '备注')
            ->setHelp('备注信息，最多100个字符')
            ->setMaxLength(100)
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
            ->add('user')
            ->add('oldLevel')
            ->add('newLevel')
            ->add('type')
            ->add('assignTime')
            ->add('createTime')
            ->add('updateTime')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('等级分配日志')
            ->setEntityLabelInPlural('等级分配日志列表')
            ->setPageTitle('index', '等级分配日志管理')
            ->setPageTitle('detail', '日志详情')
            ->setPageTitle('edit', '编辑日志')
            ->setPageTitle('new', '新建日志')
        ;
    }
}
