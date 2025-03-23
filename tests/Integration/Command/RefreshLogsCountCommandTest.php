<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\Command\RefreshLogsCountCommand;
use App\Service\LogRefresherInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class RefreshLogsCountCommandTest extends TestCase
{
    public function testExecuteSuccess(): void
    {
        $logProcessor = $this->createMock(LogRefresherInterface::class);
        $logProcessor->expects($this->once())
            ->method('refreshLogsCount');

        $command = new RefreshLogsCountCommand($logProcessor);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Logs count refreshed in cache.', $tester->getDisplay());
    }

    public function testExecuteFailure(): void
    {
        $logProcessor = $this->createMock(LogRefresherInterface::class);
        $logProcessor->expects($this->once())
            ->method('refreshLogsCount')
            ->willThrowException(new \Exception('Simulated failure'));

        $command = new RefreshLogsCountCommand($logProcessor);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Simulated failure', $tester->getDisplay());
    }
}