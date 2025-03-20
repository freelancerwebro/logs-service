<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\LogProcessorService;
use Psr\Cache\CacheItemPoolInterface;
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
        private readonly LogProcessorService $logProcessorService,
        private readonly CacheItemPoolInterface $cache
    ) {
        parent::__construct();
    }
    protected function configure(): void
    {
        $this
            ->setDescription('Process the aggregated log file')
            ->addArgument('filePath', InputArgument::REQUIRED, 'Path to the log file')
            ->addArgument('startLine', InputArgument::REQUIRED, 'Start line number')
            ->addArgument('endLine', InputArgument::REQUIRED, 'End line number');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('filePath');
        $startLine = (int) $input->getArgument('startLine');
        $endLine = (int) $input->getArgument('endLine');

        try {
            if (!is_readable($filePath)) {
                $output->write('File not found or not readable');
                return Command::FAILURE;
            }

            //$this->cache->clear();

            $this->logProcessorService->process($filePath, $startLine, $endLine);

            $output->writeln("Processed lines $startLine - $endLine");
            $output->writeln("\r\n");
        } catch (Throwable $throwable) {
            $output->writeln($throwable->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}