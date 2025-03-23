<?php

declare(strict_types=1);

namespace App\Library\StreamReader;

class TailStreamReader implements StreamReaderInterface
{
    private mixed $handle = null;

    public function open(string $filePath): void
    {
        $this->handle = popen("tail -F " . escapeshellarg($filePath) . " 2>/dev/null", "r");

        if (!$this->handle) {
            throw new \RuntimeException("Failed to open stream: $filePath");
        }
    }

    public function getNextLine(): ?string
    {
        if (feof($this->handle)) {
            return null;
        }

        $line = fgets($this->handle);
        return $line !== false ? $line : null;
    }

    public function close(): void
    {
        if (is_resource($this->handle)) {
            pclose($this->handle);
        }
    }
}
