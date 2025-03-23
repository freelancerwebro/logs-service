<?php

declare(strict_types=1);

namespace App\Command;

use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\When;
use Throwable;

#[When('dev')]
#[AsCommand(
    name: 'app:generate-logs',
    description: 'The command helps to generate random aggregated logs. To be used for testing purposes.'
)]
class GenerateLogsCommand extends Command
{
    const LOG_FORMAT = '%s - - [%s] "%s %s HTTP/1.1" %d';
    const SERVICE_NAMES = ['USER-SERVICE', 'AUTH-SERVICE', 'NOTIFICATION-SERVICE', 'INVOICE-SERVICE', 'PAYMENT-SERVICE'];
    const METHODS = ['GET', 'POST', 'PUT', 'DELETE'];
    const CODES = [200, 201, 200, 201, 200, 201, 200, 201, 200, 201, 200, 201, 204, 400, 404, 500, 502, 503];
    const ENDPOINTS = ['/auth', '/user', '/user/1', '/notify', '/invoice/2', '/payment/1/status'];

    protected function configure(): void
    {
        $this
            ->setDescription('Process a chunk of the log file')
            ->addArgument('filePath', InputArgument::REQUIRED, 'Path to the log file')
            ->addArgument('generateRowsNo', InputArgument::OPTIONAL, 'Numbers of rows to be generated', 100000);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('filePath');
        $generateRowsNo = $input->getArgument('generateRowsNo');

        try {
            for ($i = 0; $i < $generateRowsNo; ++$i) {
                $serviceName = self::SERVICE_NAMES[array_rand(self::SERVICE_NAMES)];
                $method = self::METHODS[array_rand(self::METHODS)];
                $code = self::CODES[array_rand(self::CODES)];
                $endpoint = self::ENDPOINTS[array_rand(self::ENDPOINTS)];
                $timestamp = rand(strtotime("2020-01-01 00:00:00"), strtotime("2025-03-23 00:00:00"));

                $logEntry = sprintf(self::LOG_FORMAT, $serviceName, date("d/M/Y:H:i:s O", $timestamp), $method, $endpoint, $code) . "\n";
                $this->writeIntoFile($logEntry, $filePath);
            }

            $output->write('Logs generated successfully');
        } catch (Throwable $throwable) {
            $output->write($throwable->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @throws Exception
     */
    private function writeIntoFile(string $logEntry, string $filePath): void
    {
        if (!is_writable($filePath) && !is_writable(dirname($filePath))) {
            throw new Exception("Error: Cannot write to log file.");
        }

        if (empty($logEntry)) {
            throw new Exception("Error: Log entry is empty.");
        }

        $file = fopen($filePath, 'a');

        if (!$file) {
            throw new Exception("Error: Unable to write to the log file.");
        }

        fwrite($file, $logEntry);
        fclose($file);
    }
}
