<?php

declare(strict_types=1);

namespace App\Command;

use App\Factory\LogFactoryInterface;
use App\Repository\LogRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When('dev')]
#[AsCommand(
    name: 'app:generate-logs',
    description: 'This is a test command to be used in DEV only. This purpose is just to populate the DB.',
)]
class GenerateLogsCommand extends Command
{
    public function __construct(
        private readonly LogRepositoryInterface $logRepository,
        private readonly LogFactoryInterface $logFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $serviceNames = ['USER-SERVICE', 'AUTH-SERVICE', 'NOTIFICATION-SERVICE', 'INVOICE-SERVICE', 'PAYMENT-SERVICE'];
        $methods = ['GET', 'POST', 'PUT', 'DELETE'];
        $codes = [200, 201, 200, 201, 200, 201, 200, 201, 200, 201, 200, 201, 204, 400, 404, 500, 502, 503];
        $endpoints = ['/auth', '/user', '/user/1', '/notify', '/invoice/2', '/payment/1/status'];

        for ($i = 0; $i <= 100000; ++$i) {
            $serviceName = $serviceNames[array_rand($serviceNames)];
            $method = $methods[array_rand($methods)];
            $code = $codes[array_rand($codes)];
            $endpoint = $endpoints[array_rand($endpoints)];

            $entity = $this->logFactory->create([
                'serviceName' => $serviceName,
                'method' => $method,
                'statusCode' => $code,
                'endpoint' => $endpoint,
                'created' => '2024-03-11 12:33:33',
                ]);

            $this->logRepository->save($entity);
        }

        return 0;
    }
}
