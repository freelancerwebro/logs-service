<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Log;

/**
 * @phpstan-type LogArray array{
 *      serviceName:string,
 *      method: string,
 *      endpoint: string,
 *      statusCode: int,
 *      created: string
 * }
 */
interface LogFactoryInterface
{
    /**
     * @param LogArray $data
     */
    public function create(array $data): Log;
}
