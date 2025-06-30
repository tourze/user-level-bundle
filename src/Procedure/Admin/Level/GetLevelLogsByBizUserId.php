<?php

namespace UserLevelBundle\Procedure\Admin\Level;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\JsonRPCPaginatorBundle\Procedure\PaginatorTrait;
use UserLevelBundle\Entity\AssignLog;
use UserLevelBundle\Repository\AssignLogRepository;

#[MethodTag(name: '会员中心')]
#[Log]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodDoc(summary: '拉取用户等级变更记录列表')]
#[MethodExpose(method: 'GetLevelLogsByBizUserId')]
class GetLevelLogsByBizUserId extends BaseProcedure
{
    use PaginatorTrait;

    #[MethodParam(description: '用户ID')]
    public string $userId;

    public function __construct(
        private readonly AssignLogRepository $repository,
        private readonly UserLoaderInterface $userLoader,
    ) {
    }

    public function execute(): array
    {
        $user = $this->userLoader->loadUserByIdentifier($this->userId);
        $qb = $this->repository->createQueryBuilder('a');
        if ($this->userId !== '') {
            $qb->andWhere('a.user = :user')->setParameter('user', $user);
        }
        $qb->orderBy('a.id', 'DESC');

        return $this->fetchList($qb, $this->formatItem(...));
    }

    private function formatItem(AssignLog $item): array
    {
        return $item->retrieveAdminArray();
    }
}
