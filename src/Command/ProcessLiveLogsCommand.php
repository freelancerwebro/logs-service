<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\LogLiveProcessorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'app:process-live-logs',
    description: 'Continuously process new log entries in real-time',
)]
class ProcessLiveLogsCommand extends Command
{
    public function __construct(
        private readonly LogLiveProcessorService $logProcessorService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Continuously process new log entries in real-time')
            ->addArgument('filePath', InputArgument::REQUIRED, 'Path to the log file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('filePath');

        if (!file_exists($filePath)) {
            $output->writeln("<error>Log file does not exist: $filePath</error>");
            return Command::FAILURE;
        }

        try {
            $output->writeln("<info>Watching log file: $filePath</info>");
            $this->logProcessorService->process($filePath);
        } catch (Throwable $throwable) {
            $output->writeln($throwable->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}