<?php

namespace UserLevelBundle\Procedure\Admin\Level;

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
#[MethodDoc('删除用户等级')]
#[MethodExpose('AdminDeleteLevel')]
class AdminDeleteLevel extends BaseProcedure
{
    #[MethodParam('id')]
    public string $id;

    public function __construct(
        private readonly LevelRepository $repository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $record = $this->repository->findOneBy(['id' => $this->id]);
        if (!$record) {
            throw new ApiException('记录不存在');
        }

        $this->entityManager->remove($record);
        $this->entityManager->flush();

        return ['__message' => '删除成功'];
    }
}
