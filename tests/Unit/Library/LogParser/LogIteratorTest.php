<?php

declare(strict_types=1);

namespace App\Tests\Unit\Library\LogParser;

use App\Library\LogParser\LogIterator;
use Generator;
use PHPUnit\Framework\TestCase;

final class LogIteratorTest extends TestCase
{
    public function testResourceLoadedCorrectly(): void
    {
        $iterator = new LogIterator(__DIR__.'/Files/empty_file.log');

        $handler = $iterator->getFileHandler();

        $this->assertTrue(is_resource($handler));
    }

    public function testEmptyFile(): void
    {
        $iterator = new LogIterator(__DIR__.'/Files/empty_file.log');
        $lines = $iterator->getLines();

        $result = [];
        foreach ($lines as $line) {
            $result[] = $line;
        }

        $this->assertInstanceOf(Generator::class, $lines);
        $this->assertEmpty($result);
    }

    public function testInvalidFile(): void
    {
        $iterator = new LogIterator(__DIR__.'/Files/invalid_file.log');
        $lines = $iterator->getLines();

        $result = [];
        foreach ($lines as $line) {
            $result[] = $line;
        }

        $this->assertInstanceOf(Generator::class, $lines);
        $this->assertCount(3, $result);
    }

    public function testValidFile(): void
    {
        $iterator = new LogIterator(__DIR__.'/Files/valid_file.log');
        $lines = $iterator->getLines();

        $result = [];
        foreach ($lines as $line) {
            $result[] = $line;
        }

        $this->assertInstanceOf(Generator::class, $lines);
        $this->assertCount(2, $result);
    }
}
