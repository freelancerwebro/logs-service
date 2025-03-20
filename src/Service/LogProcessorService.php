<?php

declare(strict_types=1);

namespace App\Service;

use App\Library\LogParser\LineParserInterface;
use App\Repository\LogRepositoryInterface;
use DateTime;
use Exception;

readonly class LogProcessorService
{
    const BATCH_SIZE = 1000;
    public function __construct(
        private LineParserInterface $parser,
        private LogRepositoryInterface $logRepository
    ) {
    }

    /**
     * @throws Exception
     */
    public function process(string $filePath, int $startLine, int $endLine): void
    {
        $handle = fopen($filePath, "r");
        if (!$handle) {
            throw new Exception("Failed to open file: $filePath");
        }

        $lastProcessedLine = $this->logRepository->getLastProcessedLine();

        if ($lastProcessedLine >= $startLine) {
            $startLine = $lastProcessedLine + 1;
        }
        $currentLine = 0;
        $logBuffer = [];

        while (($line = fgets($handle)) !== false) {
            $currentLine++;

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

        fclose($handle);
    }

    private function getSQLInsertString(array $lineArray): string
    {
        $created = DateTime::createFromFormat('d/M/Y:H:i:s O', $lineArray['created'])
            ->format('Y-m-d H:i:s');

        return "('" . addslashes($lineArray['serviceName']) . "', '" .
            $lineArray['method'] . "', '" . addslashes($lineArray['endpoint']) . "', '".
            (int)$lineArray['statusCode'] . "', '" . $created . "')";
    }

    public function refreshLogsCount(): void
    {
        $this->logRepository->refreshLogsCount();
    }

    /**
     * @throws Exception
     */
    public function processLiveLogs(string $filePath): void
    {
        $handle = popen("tail -F " . escapeshellarg($filePath), "r");

        if (!$handle) {
            throw new \Exception("Failed to open file: $filePath");
        }

        $logBuffer = [];

        while (!feof($handle)) {
            $line = fgets($handle);
            if (!$line) {
                usleep(100000);
                continue;
            }

            $lineArray = $this->parser->parseLine($line);
            if (!$lineArray) {
                continue;
            }

            $logBuffer[] = $this->getSQLInsertString($lineArray);

            if (count($logBuffer) >= self::BATCH_SIZE) {
                $this->logRepository->flushBulkInsert($logBuffer);
                $logBuffer = [];
            }
        }

        if (!empty($logBuffer)) {
            $this->logRepository->flushBulkInsert($logBuffer);
        }

        pclose($handle);
    }
}
