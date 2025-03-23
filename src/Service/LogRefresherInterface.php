<?php

declare(strict_types=1);

namespace App\Service;

interface LogRefresherInterface
{
    public function refreshLogsCount(): void;
}
