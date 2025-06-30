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
#[MethodDoc(summary: '批量删除用户等级')]
#[MethodExpose(method: 'AdminBatchDeleteLevel')]
class AdminBatchDeleteLevel extends BaseProcedure
{
    #[MethodParam(description: 'ids')]
    public array $ids;

    public function __construct(
        private readonly LevelRepository $repository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $records = $this->repository->findBy(['id' => $this->ids]);
        if (count($records) === 0) {
            throw new ApiException('记录不存在');
        }
        try {
            foreach ($records as $record) {
                $this->entityManager->remove($record);
            }
            $this->entityManager->flush();
        } catch (\Exception $error) {
            throw new ApiException('批量删除失败~');
        }

        return ['__message' => '删除成功'];
    }
}
