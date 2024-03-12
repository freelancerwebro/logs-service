<?php

declare(strict_types=1);

namespace App\Library\LogParser;

use App\Library\LogParser\Exception\ParserException;
use Generator;

class LogIterator implements LogIteratorInterface
{
    private mixed $fileHandler;
    private const FILE_MODE = 'r';

    public function __construct(
        private readonly string $logFile,
    ) {
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @throws ParserException
     */
    public function getFileHandler(): mixed
    {
        if (isset($this->fileHandler)) {
            return $this->fileHandler;
        }

        $fileHandler = fopen($this->logFile, self::FILE_MODE);

        if (false === $fileHandler) {
            throw new ParserException('File cannot be opened.');
        }

        if (is_resource($fileHandler)) {
            return $this->fileHandler = $fileHandler;
        }

        return null;
    }

    /**
     * @throws ParserException
     */
    public function getLines(): Generator
    {
        $fileHandler = $this->getFileHandler();

        try {
            if (!is_resource($fileHandler)) {
                throw new ParserException('File handler should be a resource.');
            }

            while ($line = fgets($fileHandler)) {
                yield $line;
            }
        } finally {
            $this->close();
        }
    }

    private function close(): void
    {
        if (isset($this->fileHandler)
            && is_resource($this->fileHandler)
        ) {
            fclose($this->fileHandler);
        }
    }
}
