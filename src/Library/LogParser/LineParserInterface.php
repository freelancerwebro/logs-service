<?php

declare(strict_types=1);

namespace App\Library\LogParser;

interface LineParserInterface
{
    /**
     * @return array<mixed>
     */
    public function parseLine(string $line): array;
}
