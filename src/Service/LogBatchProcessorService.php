<?php

declare(strict_types=1);

namespace App\Service;

use App\Library\LogParser\LineParserInterface;
use App\Library\StreamReader\StreamReaderInterface;
use App\Repository\LogRepositoryInterface;

readonly class LogBatchProcessorService extends LogProcessorAbstract implements LogRefresherInterface, LogProcessorInterface
{
    public function __construct(
        private LineParserInterface $parser,
        private LogRepositoryInterface $logRepository,
        private StreamReaderInterface $fileStreamReader,
    ) {
    }

    public function process(string $filePath, int $startLine = 0, int $endLine = 0): void
    {
        $this->fileStreamReader->open($filePath);

        $lastProcessedLine = $this->logRepository->getLastProcessedLine();

        if ($lastProcessedLine >= $startLine) {
            $startLine = $lastProcessedLine + 1;
        }
        $currentLine = 0;
        $logBuffer = [];

        while ($line = $this->fileStreamReader->getNextLine()) {
            ++$currentLine;

            if ($currentLine < $startLine) {
                continue; // Skip lines already processed
            }

            if ($currentLine > $endLine) {
                break; // Stop at the defined range
            }

            $lineArray = $this->parser->parseLine($line);
            if (!$lineArray) {
                continue;
            }

            $logBuffer[] = $this->getSQLInsertString($lineArray);

            if (count($logBuffer) >= self::BATCH_SIZE) {
                $this->logRepository->flushBulkInsert($logBuffer);
                $this->logRepository->saveLastProcessedLine($currentLine);
                $logBuffer = [];
            }
        }

        if (!empty($logBuffer)) {
            $this->logRepository->flushBulkInsert($logBuffer);
            $this->logRepository->saveLastProcessedLine($currentLine);
        }

        $this->fileStreamReader->close();
    }

    public function refreshLogsCount(): void
    {
        $this->logRepository->refreshLogsCount();
    }
}
