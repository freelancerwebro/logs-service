<?php

declare(strict_types=1);

namespace App\Service;

use App\Library\LogParser\LineParserInterface;
use App\Repository\LogRepositoryInterface;
use DateTime;
use Exception;

readonly class LogProcessorService
{
    public function __construct(
        private LineParserInterface $parser,
        private LogRepositoryInterface $logRepository
    ) {
    }

    /**
     * @throws Exception
     */
    public function process(string $filePath): void
    {
        $handle = fopen($filePath, "r");
        if (!$handle) {
            throw new Exception("Failed to open file: $filePath");
        }

        $batchSize = 1000;
        $logBuffer = [];

        while (($line = fgets($handle)) !== false) {
            $lineArray = $this->parser->parseLine($line);
            if (!$lineArray) {
                continue;
            }

            $logBuffer[] = $this->getSQLInsertString($lineArray);

            if (count($logBuffer) >= $batchSize) {
                $this->logRepository->flushBulkInsert($logBuffer);
                $logBuffer = [];
            }
        }

        if (!empty($logBuffer)) {
            $this->logRepository->flushBulkInsert($logBuffer);
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
}
