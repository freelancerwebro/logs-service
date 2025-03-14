<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\LogProcessorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'app:process-logs',
    description: 'Read the log file and write the data to the Database',
)]
final class ProcessLogsCommand extends Command
{
    public function __construct(
        private readonly LogProcessorService $logProcessorService
    ) {
        parent::__construct();
    }
    protected function configure(): void
    {
        $this
            ->setDescription('Process the aggregated log file')
            ->addArgument('filePath', InputArgument::REQUIRED, 'Path to the log file');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('filePath');

        try {
            if (!is_readable($filePath)) {
                $output->write('File not found or not readable');
                return Command::FAILURE;
            }

            $this->logProcessorService->process($filePath);

            $output->write('Process logs executed successfully');
        } catch (Throwable $throwable) {
            $output->write($throwable->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}