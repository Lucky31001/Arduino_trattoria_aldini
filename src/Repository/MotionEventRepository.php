<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MotionEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MotionEvent>
 * @method MotionEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method MotionEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method MotionEvent[]    findAll()
 * @method MotionEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MotionEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MotionEvent::class);
    }

    /** @return array<int, array<string, mixed>> */
    public function findRecentForApi(int $limit = 100): array
    {
        return $this->createQueryBuilder('m')
            ->join('m.device', 'd')
            ->select('m.id AS id', 'd.deviceId AS device_id', 'COALESCE(d.name, d.deviceId) AS device_name', 'm.detectedAt AS detected_at')
            ->orderBy('m.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }

    public function save(MotionEvent $motionEvent): void
    {
        $this->getEntityManager()->persist($motionEvent);
        $this->getEntityManager()->flush();
    }
}

