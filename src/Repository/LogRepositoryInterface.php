<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\LogRequestDto;
use App\Entity\Log;

interface LogRepositoryInterface
{
    public function countByCriteria(?LogRequestDto $logRequestDto = null): int;

    public function deleteAll(): int;

    public function save(Log $log): void;

    public function flushBulkInsert(array $logBuffer): void;
    public function getPaginatedLogs(int $page, int $limit): array;

    public function getLastProcessedLine(): int;
    public function saveLastProcessedLine(int $lineNumber): void;
}
