<?php

namespace UserLevelBundle\Procedure;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPC\Core\Result\ArrayResult;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\JsonRPCPaginatorBundle\Procedure\PaginatorTrait;
use UserLevelBundle\Entity\AssignLog;
use UserLevelBundle\Param\GetLevelLogsByBizUserIdParam;
use UserLevelBundle\Repository\AssignLogRepository;

#[MethodTag(name: '会员中心')]
#[Log]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodDoc(summary: '拉取用户等级变更记录列表')]
#[MethodExpose(method: 'GetLevelLogsByBizUserId')]
final class GetLevelLogsByBizUserId extends BaseProcedure
{
    use PaginatorTrait;

    public function __construct(
        private readonly AssignLogRepository $repository,
        private readonly UserLoaderInterface $userLoader,
    ) {
    }

    /**
     * @phpstan-param GetLevelLogsByBizUserIdParam $param
     */
    public function execute(GetLevelLogsByBizUserIdParam|RpcParamInterface $param): ArrayResult
    {
        $user = $this->userLoader->loadUserByIdentifier($param->userId);
        $qb = $this->repository->createQueryBuilder('a');
        if ('' !== $param->userId) {
            $qb->andWhere('a.user = :user')->setParameter('user', $user);
        }
        $qb->orderBy('a.id', 'DESC');

        return new ArrayResult($this->fetchList($qb, $this->formatItem(...), null, $param));
    }

    /**
     * @return array<string, mixed>
     */
    private function formatItem(AssignLog $item): array
    {
        return $item->retrieveAdminArray();
    }
}
