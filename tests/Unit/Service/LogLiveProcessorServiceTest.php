<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Library\LogParser\LineParserInterface;
use App\Library\StreamReader\StreamReaderInterface;
use App\Service\LogLiveProcessorService;
use App\Repository\LogRepositoryInterface;
use PHPUnit\Framework\TestCase;

class LogLiveProcessorServiceTest extends TestCase
{
    public function testProcessFlushesParsedLinesInBatches(): void
    {
        $reader = $this->createMock(StreamReaderInterface::class);
        $parser = $this->createMock(LineParserInterface::class);
        $repository = $this->createMock(LogRepositoryInterface::class);

        $reader->method('getNextLine')
            ->willReturnOnConsecutiveCalls('AUTH-SERVICE - - [14/Mar/2025:22:43:01 +0000] "GET /test HTTP/1.1" 200');

        $reader->expects($this->once())->method('open')->with('/fake/path.log');
        $reader->expects($this->once())->method('close');

        $parser->method('parseLine')
            ->willReturn([
                'serviceName' => 'AUTH-SERVICE',
                'method' => 'GET',
                'endpoint' => '/test',
                'statusCode' => 200,
                'created' => '14/Mar/2025:22:43:01 +0000'
            ]);

        $repository->expects($this->once())
            ->method('flushBulkInsert')
            ->with($this->callback(function ($buffer) {
                return is_array($buffer)
                    && count($buffer) === 1
                    && str_contains($buffer[0], "'AUTH-SERVICE'")
                    && str_contains($buffer[0], "'/test'")
                    && str_contains($buffer[0], "'200'")
                    && str_contains($buffer[0], '2025-03-14 22:43:01');
            }));

        $service = new LogLiveProcessorService($parser, $repository, $reader);
        $service->process('/fake/path.log');
    }

    public function testProcessSkipsInvalidParsedLines(): void
    {
        $reader = $this->createMock(StreamReaderInterface::class);
        $parser = $this->createMock(LineParserInterface::class);
        $repository = $this->createMock(LogRepositoryInterface::class);

        $reader->method('getNextLine')
            ->willReturnOnConsecutiveCalls('line1', 'line2', null);

        $reader->expects($this->once())->method('open');
        $reader->expects($this->once())->method('close');

        $parser->method('parseLine')
            ->willReturnOnConsecutiveCalls([], [
                'serviceName' => 'svc',
                'method' => 'POST',
                'endpoint' => '/create',
                'statusCode' => 201,
                'created' => '14/Mar/2025:22:43:01 +0000'
            ]);

        $repository->expects($this->once())
            ->method('flushBulkInsert')
            ->with($this->callback(fn ($items) => count($items) === 1));

        $service = new LogLiveProcessorService($parser, $repository, $reader);
        $service->process('/some/file.log');
    }
}