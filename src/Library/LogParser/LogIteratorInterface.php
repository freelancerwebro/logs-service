<?php

declare(strict_types=1);

namespace App\Library\LogParser;

use Generator;

interface LogIteratorInterface
{
    public function getFileHandler(): mixed;

    public function getLines(): Generator;
}
