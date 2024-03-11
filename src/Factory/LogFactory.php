<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Log;
use DateTimeImmutable;

/**
 * @phpstan-import-type LogArray from LogFactoryInterface
 */
class LogFactory implements LogFactoryInterface
{
    /**
     * @param LogArray $data
     */
    public function create(array $data): Log
    {
        $log = new Log();
        $log->setServiceName($data['serviceName']);
        $log->setMethod($data['method']);
        $log->setEndpoint($data['endpoint']);
        $log->setStatusCode((int) $data['statusCode']);
        $log->setCreated(new DateTimeImmutable($data['created']));

        return $log;
    }
}
