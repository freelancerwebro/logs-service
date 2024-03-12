<?php

declare(strict_types=1);

namespace App\Service;

use App\Factory\LogFactoryInterface;
use App\Library\LogParser\Exception\ParserException;
use App\Library\LogParser\LineParserInterface;
use App\Library\LogParser\LogIteratorInterface;
use App\Repository\LogRepositoryInterface;
use App\Service\Exception\LineInvalidServiceException;

/**
 * @phpstan-import-type LogArray from LogFactoryInterface
 */
final readonly class SaveLogService implements SaveLogServiceInterface
{
    public function __construct(
        private LogRepositoryInterface $logRepository,
        private LogFactoryInterface $logFactory,
        private LineParserInterface $parser,
        private LogIteratorInterface $logIterator
    ) {
    }

    /**
     * @throws LineInvalidServiceException
     * @throws ParserException
     */
    public function save(): void
    {
        foreach ($this->logIterator->getLines() as $line) {
            if (!is_string($line)) {
                throw new LineInvalidServiceException();
            }

            $this->saveOneLine($line);
        }
    }

    private function saveOneLine(string $line): void
    {
        $lineArray = $this->parser->parseLine($line);

        /**
         * @var LogArray $lineArray
         */
        $entity = $this->logFactory->create($lineArray);

        $this->logRepository->save($entity);
    }
}
