<?php

namespace UserLevelBundle\Procedure\Admin\Level;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\JsonRPCPaginatorBundle\Procedure\PaginatorTrait;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Repository\LevelRepository;

#[MethodTag('会员中心')]
#[Log]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodDoc('拉取用户等级列表')]
#[MethodExpose('AdminGetLevelList')]
class AdminGetLevelList extends BaseProcedure
{
    use PaginatorTrait;

    #[MethodParam('等级名称')]
    public string $title = '';

    public function __construct(
        private readonly LevelRepository $repository,
    ) {
    }

    public function execute(): array
    {
        $qb = $this->repository->createQueryBuilder('a');
        if ($this->title) {
            $qb->andWhere('a.title like :title')->setParameter('title', "%{$this->title}%");
        }
        $qb->orderBy('a.id', 'DESC');

        return $this->fetchList($qb, $this->formatItem(...));
    }

    private function formatItem(Level $item): array
    {
        return $item->retrieveAdminArray();
    }
}
