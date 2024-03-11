<?php

declare(strict_types=1);

namespace App\Service;

use App\Factory\LogFactoryInterface;
use App\Library\LogParser\Exception\ServiceLogException;
use App\Repository\LogRepositoryInterface;
use MVar\LogParser\LogIterator;
use Throwable;

/**
 * @phpstan-import-type LogArray from LogFactoryInterface
 */
final readonly class SaveLogService implements SaveLogServiceInterface
{
    public function __construct(
        private LogIterator $logIterator,
        private LogRepositoryInterface $logRepository,
        private LogFactoryInterface $logFactory,
    ) {
    }

    /**
     * @throws ServiceLogException
     */
    public function save(): void
    {
        try {
            /**
             * @var LogArray $data
             */
            foreach ($this->logIterator as $data) {
                $entity = $this->logFactory->create($data);
                $this->logRepository->save(
                    $entity
                );
            }
        } catch (Throwable $exception) {
            throw new ServiceLogException($exception->getMessage());
        }
    }
}
