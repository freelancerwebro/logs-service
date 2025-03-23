<?php

declare(strict_types=1);

namespace App\Tests\Unit\Library\StreamReader;

use App\Library\StreamReader\FileStreamReader;
use PHPUnit\Framework\TestCase;

class FileStreamReaderTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'filestream_');
        file_put_contents($this->tempFile, "line 1\nline 2\nline 3\n");
    }

    protected function tearDown(): void
    {
        @unlink($this->tempFile);
    }

    public function testOpenThrowsExceptionOnInvalidPath(): void
    {
        $reader = new FileStreamReader();

        $this->expectException(\RuntimeException::class);
        @$reader->open('/nonexistent/file/path.log');
    }

    public function testGetNextLineReturnsEachLine(): void
    {
        $reader = new FileStreamReader();
        $reader->open($this->tempFile);

        $this->assertSame("line 1\n", $reader->getNextLine());
        $this->assertSame("line 2\n", $reader->getNextLine());
        $this->assertSame("line 3\n", $reader->getNextLine());
        $this->assertNull($reader->getNextLine()); // End of file

        $reader->close();
    }

    public function testCloseIsIdempotent(): void
    {
        $reader = new FileStreamReader();
        $reader->open($this->tempFile);
        $reader->close();

        $this->assertTrue(true);
    }
}