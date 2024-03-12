<?php

declare(strict_types=1);

namespace App\Library\LogParser;

use App\Library\LogParser\Exception\ParserException;
use App\Library\LogParser\Exception\PatternMatchException;

abstract class AbstractParser implements LineParserInterface
{
    /**
     * @return array<mixed>
     *
     * @throws ParserException
     * @throws PatternMatchException
     */
    public function parseLine(string $line): array
    {
        $match = preg_match($this->getPattern(), $line, $matches);

        if (false === $match) {
            throw new PatternMatchException('The given line is invalid according to the pattern used.');
        }

        return $this->prepareParsedData($matches);
    }

    /**
     * @param array<mixed> $matches
     *
     * @return array<mixed>
     */
    protected function prepareParsedData(array $matches): array
    {
        $filtered = array_filter(array_keys($matches), 'is_string');
        $result = array_intersect_key($matches, array_flip($filtered));

        return array_filter($result);
    }

    abstract protected function getPattern(): string;
}
