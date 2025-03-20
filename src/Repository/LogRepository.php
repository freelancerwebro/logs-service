<?php

namespace App\Repository;

use App\Dto\LogRequestDto;
use App\Entity\Log;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @extends ServiceEntityRepository<Log>
 *
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository implements LogRepositoryInterface
{
    private const CACHE_LIFETIME = 3600;
    private const CACHE_LAST_PROCESSED_LINE_KEY = 'last_processed_line';
    private const CACHE_LOGS_COUNT_KEY = 'logs_count';

    private Connection $conn;
    private CacheInterface $cache;

    public function __construct(ManagerRegistry $registry, Connection $conn, CacheInterface $cache)
    {
        parent::__construct($registry, Log::class);
        $this->conn = $conn;
        $this->cache = $cache;
    }

    public function countByCriteria(?LogRequestDto $logRequestDto = null): int
    {
        $cacheKey = $this->generateCacheKey($logRequestDto);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($logRequestDto) {
            $item->expiresAfter(self::CACHE_LIFETIME);

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

            return (int)$qb->getQuery()->getSingleScalarResult();
        });
    }

    private function generateCacheKey(?LogRequestDto $logRequestDto): string
    {
        if (empty($logRequestDto->serviceNames) &&
            empty($logRequestDto->statusCode) &&
            empty($logRequestDto->startDate) &&
            empty($logRequestDto->endDate)
        ) {
            return self::CACHE_LOGS_COUNT_KEY;
        }

        return 'logs_count_' . md5(json_encode([
                'serviceNames' => $logRequestDto->serviceNames ?? '',
                'statusCode' => $logRequestDto->statusCode ?? '',
                'startDate' => $logRequestDto->startDate ?? '',
                'endDate' => $logRequestDto->endDate ?? ''
            ]));
    }

    public function deleteAll(): int
    {
        $qb = $this->createQueryBuilder('l');
        $qb->delete();

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function save(Log $log): void
    {
        $this->getEntityManager()->persist($log);
        $this->getEntityManager()->flush();
    }

    public function clearCache(): void
    {
        $this->cache->clear();
    }

    public function flushBulkInsert(array $logBuffer): void
    {
        if (empty($logBuffer)) {
            return;
        }

        try {
            $sql = "INSERT INTO log (service_name, method, endpoint, status_code, created) VALUES " . implode(", ", $logBuffer);
            $this->conn->executeStatement($sql);
        } catch (Exception $e) {
            throw new RuntimeException('Failed to insert logs: ' . $e->getMessage());
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getTotalLogsCount(): int
    {
        return $this->cache->get(self::CACHE_LOGS_COUNT_KEY, function (ItemInterface $item) {
            $item->expiresAfter(self::CACHE_LIFETIME); // Cache for 1 hour

            return $this->calculateLogsCount();
        });
    }

    public function calculateLogsCount(): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function refreshLogsCount(): void
    {
        $this->cache->delete(self::CACHE_LOGS_COUNT_KEY);

        $this->getTotalLogsCount();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getPaginatedLogs(int $page, int $limit): array
    {
        return $this->cache->get("logs_page_{$page}_limit_{$limit}", function (ItemInterface $item) use ($page, $limit) {
            $item->expiresAfter(self::CACHE_LIFETIME);

            $query = $this->createQueryBuilder('l')
                ->select('l.id, l.serviceName, l.statusCode, l.endpoint, l.method, l.created')
                ->orderBy('l.created', 'DESC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery();

            return $query->getResult();
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getLastProcessedLine(): int
    {
        return (int) $this->cache->get(self::CACHE_LAST_PROCESSED_LINE_KEY, fn() => 0);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function saveLastProcessedLine(int $lineNumber): void
    {
        $this->cache->delete(self::CACHE_LAST_PROCESSED_LINE_KEY);
        $this->cache->get(self::CACHE_LAST_PROCESSED_LINE_KEY, fn() => $lineNumber);
    }
}
