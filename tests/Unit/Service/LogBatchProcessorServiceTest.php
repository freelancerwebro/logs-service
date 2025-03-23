<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Library\LogParser\LineParserInterface;
use App\Library\StreamReader\StreamReaderInterface;
use App\Repository\LogRepositoryInterface;
use App\Service\LogBatchProcessorService;
use PHPUnit\Framework\TestCase;

class LogBatchProcessorServiceTest extends TestCase
{
    public function testProcessFlushesValidParsedLinesAndSkipsInvalidOnes(): void
    {
        $reader = $this->createMock(StreamReaderInterface::class);
        $parser = $this->createMock(LineParserInterface::class);
        $repository = $this->createMock(LogRepositoryInterface::class);

        $repository->method('getLastProcessedLine')->willReturn(0);

        $reader->method('getNextLine')->willReturnOnConsecutiveCalls(
            'line1', 'line2', 'line3', null
        );

        $reader->expects($this->once())->method('open')->with('/fake/file.log');
        $reader->expects($this->once())->method('close');

        $parser->method('parseLine')->willReturnOnConsecutiveCalls(
            [
                'serviceName' => 'auth',
                'method' => 'GET',
                'endpoint' => '/test',
                'statusCode' => 200,
                'created' => '14/Mar/2025:22:43:01 +0000'
            ],
            [],
            [
                'serviceName' => 'api',
                'method' => 'POST',
                'endpoint' => '/login',
                'statusCode' => 201,
                'created' => '14/Mar/2025:22:45:00 +0000'
            ]
        );

        $repository->expects($this->once())
            ->method('flushBulkInsert')
            ->with($this->callback(fn ($items) => count($items) === 2));

        $repository->expects($this->once())
            ->method('saveLastProcessedLine')
            ->with(3);

        $service = new LogBatchProcessorService($parser, $repository, $reader);
        $service->process('/fake/file.log', 1, 100);
    }

    public function testSkipsLinesBelowLastProcessedLine(): void
    {
        $reader = $this->createMock(StreamReaderInterface::class);
        $parser = $this->createMock(LineParserInterface::class);
        $repository = $this->createMock(LogRepositoryInterface::class);

        $repository->method('getLastProcessedLine')->willReturn(2);

        $reader->method('getNextLine')->willReturnOnConsecutiveCalls(
            'line1', 'line2', 'line3', 'line4', null
        );

        $parser->method('parseLine')->willReturn([
            'serviceName' => 'auth',
            'method' => 'GET',
            'endpoint' => '/test',
            'statusCode' => 200,
            'created' => '14/Mar/2025:22:43:01 +0000'
        ]);

        $repository->expects($this->once())
            ->method('flushBulkInsert')
            ->with($this->callback(fn ($items) => count($items) === 2)); // lines 3 and 4

        $repository->expects($this->once())
            ->method('saveLastProcessedLine')->with(4);

        $reader->expects($this->once())->method('open');
        $reader->expects($this->once())->method('close');

        $service = new LogBatchProcessorService($parser, $repository, $reader);
        $service->process('/fake.log', 1, 10);
    }

    public function testRefreshLogsCount(): void
    {
        $reader = $this->createMock(StreamReaderInterface::class);
        $parser = $this->createMock(LineParserInterface::class);
        $repository = $this->createMock(LogRepositoryInterface::class);

        $repository->expects($this->once())->method('refreshLogsCount');

        $service = new LogBatchProcessorService($parser, $repository, $reader);
        $service->refreshLogsCount();
    }
}