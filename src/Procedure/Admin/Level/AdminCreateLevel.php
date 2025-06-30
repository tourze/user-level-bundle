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
use UserLevelBundle\Entity\Level;

#[MethodTag(name: '会员中心')]
#[Log]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[MethodDoc(summary: '创建用户等级')]
#[MethodExpose(method: 'AdminCreateLevel')]
class AdminCreateLevel extends BaseProcedure
{
    #[MethodParam(description: '等级名称')]
    public string $title;

    #[MethodParam(description: '等级值')]
    public int $level;

    #[MethodParam(description: '有效')]
    public ?bool $valid = false;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $record = new Level();
        $record->setTitle($this->title);
        $record->setLevel($this->level);
        $record->setValid($this->valid);

        try {
            $this->entityManager->persist($record);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            throw new ApiException('创建时发现重复数据', previous: $exception);
        }

        return ['__message' => '创建成功'];
    }
}
