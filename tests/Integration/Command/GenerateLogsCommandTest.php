<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\Command\GenerateLogsCommand;
use Exception;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateLogsCommandTest extends KernelTestCase
{
    private string $logFile;

    protected function setUp(): void
    {
        $this->logFile = sys_get_temp_dir().'/test_log.log';
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    public function testLogsAreGeneratedSuccessfully(): void
    {
        $command = new GenerateLogsCommand();
        $tester = new CommandTester($command);

        $tester->execute([
            'filePath' => $this->logFile,
            'generateRowsNo' => 50,
        ]);

        $tester->assertCommandIsSuccessful();
        $this->assertFileExists($this->logFile);

        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES);
        $this->assertCount(50, $lines);
        $this->assertStringContainsString('HTTP/1.1', $lines[0]);
    }

    public function testFailsIfLogEntryIsEmpty(): void
    {
        $command = new GenerateLogsCommand();
        $reflection = new ReflectionClass($command);
        $method = $reflection->getMethod('writeIntoFile');
        $method->setAccessible(true);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Log entry is empty');

        $method->invoke($command, '', $this->logFile);
    }
}
