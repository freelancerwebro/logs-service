<?php

declare(strict_types=1);

namespace App\Library\LogParser;

use MVar\LogParser\LineParserInterface;
use MVar\LogParser\LogIterator;

final class ServiceLogIterator extends LogIterator
{
    public function __construct(string $logFile, LineParserInterface $parser)
    {
        parent::__construct($logFile, $parser);
    }
}
