<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\LogRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Throwable;

#[AsCommand(name: 'app:refresh-logs-count')]
class RefreshLogsCountCommand extends Command
{
    public function __construct(
        private readonly LogRepository $logRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->logRepository->refreshLogsCount();
            $output->writeln('Logs count refreshed in cache.');
            return Command::SUCCESS;
        } catch (Throwable $throwable) {
            $output->writeln($throwable->getMessage());
            return Command::FAILURE;
        }
    }
}