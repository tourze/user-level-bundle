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

#[MethodTag(name: '会员中心')]
#[Log]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodDoc(summary: '删除用户等级')]
#[MethodExpose(method: 'AdminDeleteLevel')]
class AdminDeleteLevel extends BaseProcedure
{
    #[MethodParam(description: 'id')]
    public string $id;

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

        $this->entityManager->remove($record);
        $this->entityManager->flush();

        return ['__message' => '删除成功'];
    }
}
