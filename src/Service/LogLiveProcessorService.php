<?php

declare(strict_types=1);

namespace App\Service;

use App\Library\LogParser\LineParserInterface;
use App\Library\StreamReader\StreamReaderInterface;
use App\Repository\LogRepositoryInterface;

readonly class LogLiveProcessorService extends LogProcessorAbstract implements LogProcessorInterface
{
    public function __construct(
        private LineParserInterface $parser,
        private LogRepositoryInterface $logRepository,
        private StreamReaderInterface $tailStreamReader,
    ) {
    }
    public function process(string $filePath, int $startLine = 0, int $endLine = 0): void
    {
        $this->tailStreamReader->open($filePath);
        $logBuffer = [];

        while ($line = $this->tailStreamReader->getNextLine()) {

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

        $this->tailStreamReader->close();
    }
}