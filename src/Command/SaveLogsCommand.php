<?php

namespace App\Command;

use App\Service\SaveLogServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'app:save-logs',
    description: 'Read a log file and write it to the Database',
)]
final class SaveLogsCommand extends Command
{
    public function __construct(
        private readonly SaveLogServiceInterface $saveLogService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->saveLogService->save();
        } catch (Throwable $throwable) {
            $output->write($throwable->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
