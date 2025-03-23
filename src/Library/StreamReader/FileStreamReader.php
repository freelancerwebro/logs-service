<?php

declare(strict_types=1);

namespace App\Library\StreamReader;

use RuntimeException;

class FileStreamReader implements StreamReaderInterface
{
    private mixed $handle = null;

    public function open(string $filePath): void
    {
        $this->handle = fopen($filePath, 'r');

        if (!$this->handle || !is_resource($this->handle)) {
            throw new RuntimeException("Failed to open file: $filePath");
        }
    }

    public function getNextLine(): ?string
    {
        if (feof($this->handle)) {
            return null;
        }

        $line = fgets($this->handle);

        return false !== $line ? $line : null;
    }

    public function close(): void
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }
}
