<?php

namespace App\Command;

use App\Service\SaveLogServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

    protected function configure(): void
    {
        $this->addArgument('filepath', InputArgument::OPTIONAL, 'The path of the log file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // $io = new SymfonyStyle($input, $output);
        //        $arg1 = $input->getArgument('filepath');
        //
        //        if ($arg1) {
        //            $io->note(sprintf('You passed an argument: %s', $arg1));
        //        }
        //
        //        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        // parse file

        // save to DB

        // die($this->logFilePath . ', ' . $this->fileRegex);

        //        $handle = fopen($this->logFilePath, "r");
        //
        //        if ($handle) {
        //            while (($line = fgets($handle)) !== false) {
        //                $match = @preg_match($this->fileRegex, $line, $matches);
        //
        //                $res = $this->parseResult($matches);
        //
        //                print_r($res);
        //
        //                sleep(5);
        //            }x
        //        }

        //        $parser = new SimpleParser($this->fileRegex);
        //
        //        foreach (new LogIterator($this->logFilePath, $parser) as $data) {
        //            var_export($data);
        //            echo "\n";
        //
        //            sleep(5);
        //        }

        $this->saveLogService->save();

        return Command::SUCCESS;
    }
}
