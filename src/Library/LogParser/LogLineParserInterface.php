<?php

declare(strict_types=1);

namespace App\Library\LogParser;

interface LogLineParserInterface
{
    public function parseLine(string $line);
}
