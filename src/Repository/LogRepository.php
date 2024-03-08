<?php

namespace App\Repository;

use App\Dto\LogRequestDto;
use App\Entity\Log;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Log>
 *
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    public function countByCriteria(?LogRequestDto $logRequestDto = null): int
    {
        $qb = $this->createQueryBuilder('l');
        $qb = $qb
            ->select('count(l.id)')
            ->where('1 = 1');

        if (!empty($logRequestDto->serviceNames)) {
            $qb->andWhere($qb->expr()->in('l.serviceName', ':serviceNames'))
                ->setParameter('serviceNames', $logRequestDto->serviceNames);
        }

        if (!empty($logRequestDto->statusCode)) {
            $qb->andWhere('l.statusCode = :statusCode')
                ->setParameter('statusCode', $logRequestDto->statusCode);
        }

        if (!empty($logRequestDto->statusCode)) {
            $qb->andWhere('l.statusCode = :statusCode')
                ->setParameter('statusCode', $logRequestDto->statusCode);
        }

        if (!empty($logRequestDto->startDate)) {
            $qb->andWhere('l.created >= :startDate')
                ->setParameter('startDate', $logRequestDto->startDate);
        }

        if (!empty($logRequestDto->endDate)) {
            $qb->andWhere('l.created <= :endDate')
                ->setParameter('endDate', $logRequestDto->endDate);
        }

        $result = $qb
            ->getQuery()
            ->useQueryCache(true)
            ->setQueryCacheLifetime(3600)
            ->getSingleScalarResult();

        return (int) $result;
    }

    public function deleteAll(): int
    {
        $qb = $this->createQueryBuilder('l');
        $qb->delete();

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
