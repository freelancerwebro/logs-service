<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\Command\ProcessLogsCommand;
use App\Service\LogProcessorInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ProcessLogsCommandTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        $this->tempFile = sys_get_temp_dir().'/test_log.log';
        file_put_contents($this->tempFile, "line 1\nline 2\nline 3\n");
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testCommandProcessesLinesSuccessfully(): void
    {
        $mockProcessor = $this->createMock(LogProcessorInterface::class);
        $mockProcessor->expects($this->once())
            ->method('process')
            ->with($this->tempFile, 1, 3);

        $command = new ProcessLogsCommand($mockProcessor);
        $tester = new CommandTester($command);

        $tester->execute([
            'filePath' => $this->tempFile,
            'startLine' => 1,
            'endLine' => 3,
        ]);

        $this->assertEquals(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('Processed lines 1 - 3', $tester->getDisplay());
    }

    public function testCommandFailsOnUnreadableFile(): void
    {
        $mockProcessor = $this->createMock(LogProcessorInterface::class);

        $command = new ProcessLogsCommand($mockProcessor);
        $tester = new CommandTester($command);

        $tester->execute([
            'filePath' => '/nonexistent/file.log',
            'startLine' => 1,
            'endLine' => 3,
        ]);

        $this->assertEquals(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('File not found or not readable', $tester->getDisplay());
    }

    public function testCommandFailsOnProcessingException(): void
    {
        $mockProcessor = $this->createMock(LogProcessorInterface::class);
        $mockProcessor->expects($this->once())
            ->method('process')
            ->willThrowException(new RuntimeException('Simulated failure'));

        $command = new ProcessLogsCommand($mockProcessor);
        $tester = new CommandTester($command);

        $tester->execute([
            'filePath' => $this->tempFile,
            'startLine' => 1,
            'endLine' => 2,
        ]);

        $this->assertEquals(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Simulated failure', $tester->getDisplay());
    }
}
