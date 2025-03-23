<?php

declare(strict_types=1);

namespace App\Service;

interface LogProcessorInterface
{
    public function process(string $filePath, int $startLine = 0, int $endLine = 0): void;
}
