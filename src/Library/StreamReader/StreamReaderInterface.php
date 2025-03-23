<?php

declare(strict_types=1);

namespace App\Library\StreamReader;

interface StreamReaderInterface
{
    public function open(string $filePath): void;

    public function getNextLine(): ?string;

    public function close(): void;
}
