<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\Command\ProcessLiveLogsCommand;
use App\Service\LogProcessorInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ProcessLiveLogsCommandTest extends TestCase
{
    private string $logFile;

    protected function setUp(): void
    {
        $this->logFile = tempnam(sys_get_temp_dir(), 'log_');
        file_put_contents($this->logFile, "Initial log line\n");
    }

    protected function tearDown(): void
    {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    public function testCommandFailsIfLogFileDoesNotExist(): void
    {
        $mockService = $this->createMock(LogProcessorInterface::class);
        $command = new ProcessLiveLogsCommand($mockService);
        $tester = new CommandTester($command);

        $tester->execute([
            'filePath' => '/nonexistent/log/path.log',
        ]);

        $this->assertEquals(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Log file does not exist', $tester->getDisplay());
    }

    public function testCommandRunsSuccessfully(): void
    {
        $mockService = $this->createMock(LogProcessorInterface::class);
        $mockService->expects($this->once())
            ->method('process')
            ->with($this->logFile);

        $command = new ProcessLiveLogsCommand($mockService);
        $tester = new CommandTester($command);

        $statusCode = $tester->execute([
            'filePath' => $this->logFile,
        ]);

        $this->assertEquals(Command::SUCCESS, $statusCode);
        $this->assertStringContainsString('Watching log file', $tester->getDisplay());
    }

    public function testCommandHandlesException(): void
    {
        $mockService = $this->createMock(LogProcessorInterface::class);
        $mockService->method('process')->willThrowException(new RuntimeException('Simulated error'));

        $command = new ProcessLiveLogsCommand($mockService);
        $tester = new CommandTester($command);

        $statusCode = $tester->execute([
            'filePath' => $this->logFile,
        ]);

        $this->assertEquals(Command::FAILURE, $statusCode);
        $this->assertStringContainsString('Simulated error', $tester->getDisplay());
    }
}
