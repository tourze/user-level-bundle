<?php

namespace UserLevelBundle\Procedure\Admin\Level;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use UserLevelBundle\Repository\LevelRepository;

#[MethodTag('会员中心')]
#[Log]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodDoc('编辑用户等级')]
#[MethodExpose('AdminUpdateLevel')]
class AdminUpdateLevel extends BaseProcedure
{
    #[MethodParam('id')]
    public string $id;

    #[MethodParam('等级名称')]
    public string $title;

    #[MethodParam('等级值')]
    public int $level;

    #[MethodParam('有效')]
    public ?bool $valid = false;

    public function __construct(
        private readonly LevelRepository $repository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $record = $this->repository->findOneBy(['id' => $this->id]);
        if ($record === null) {
            throw new ApiException('记录不存在');
        }
        $record->setTitle($this->title);
        $record->setLevel($this->level);
        $record->setValid($this->valid);

        try {
            $this->entityManager->persist($record);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            throw new ApiException('更新时发现重复数据', previous: $exception);
        }

        return ['__message' => '编辑成功'];
    }
}
