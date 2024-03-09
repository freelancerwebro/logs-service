<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Log;
use App\Repository\LogRepositoryInterface;
use MVar\LogParser\LogIterator;

final readonly class SaveLogService implements SaveLogServiceInterface
{
    public function __construct(
        private LogIterator $logIterator,
        private LogRepositoryInterface $logRepository,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function save(): void
    {
        foreach ($this->logIterator as $data) {
            $this->logRepository->save(
                $this->prepareLogEntity($data)
            );
        }
    }

    /**
     * @throws \Exception
     */
    private function prepareLogEntity(array $data): Log
    {
        $log = new Log();
        $log->setServiceName($data['serviceName']);
        $log->setMethod($data['method']);
        $log->setEndpoint($data['endpoint']);
        $log->setStatusCode((int) $data['statusCode']);
        $log->setCreated(new \DateTimeImmutable($data['date']));

        return $log;
    }
}
