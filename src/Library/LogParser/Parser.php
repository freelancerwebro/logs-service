<?php

declare(strict_types=1);

namespace App\Library\LogParser;

final class Parser extends AbstractParser
{
    public function __construct(
        private readonly string $pattern
    ) {
    }

    protected function getPattern(): string
    {
        return $this->pattern;
    }
}
