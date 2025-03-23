<?php

declare(strict_types=1);

namespace App\Service;

use DateTime;

readonly abstract class LogProcessorAbstract
{
    const BATCH_SIZE = 1000;
    abstract public function process(string $filePath, int $startLine = 0, int $endLine = 0): void;

    protected function getSQLInsertString(array $lineArray): string
    {
        $created = DateTime::createFromFormat('d/M/Y:H:i:s O', $lineArray['created'])
            ->format('Y-m-d H:i:s');

        return "('" . $lineArray['serviceName'] . "', '" .
            $lineArray['method'] . "', '" . $lineArray['endpoint'] . "', '".
            (int)$lineArray['statusCode'] . "', '" . $created . "')";
    }
}