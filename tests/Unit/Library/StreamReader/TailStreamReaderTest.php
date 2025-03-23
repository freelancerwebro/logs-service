<?php

declare(strict_types=1);

namespace App\Tests\Unit\Library\StreamReader;

use App\Library\StreamReader\TailStreamReader;
use PHPUnit\Framework\TestCase;

class TailStreamReaderTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'tailtest_');
        file_put_contents($this->tempFile, "line1\nline2\n");
    }

    protected function tearDown(): void
    {
        @unlink($this->tempFile);
    }

    public function testGetNextLineReturnsLogLines(): void
    {
        $reader = new TailStreamReader();
        $reader->open($this->tempFile);

        $line1 = $reader->getNextLine();
        $this->assertNotNull($line1);
        $this->assertStringContainsString('line1', $line1);

        $line2 = $reader->getNextLine();
        $this->assertNotNull($line2);
        $this->assertStringContainsString('line2', $line2);

        $reader->close();
    }

    public function testCloseDoesNotCrashIfAlreadyClosed(): void
    {
        $reader = new TailStreamReader();
        $reader->open($this->tempFile);
        $reader->close();

        $this->assertTrue(true);
    }
}