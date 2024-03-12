<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Factory\LogFactoryInterface;
use App\Library\LogParser\LineParserInterface;
use App\Library\LogParser\LogIteratorInterface;
use App\Repository\LogRepositoryInterface;
use App\Service\Exception\LineInvalidServiceException;
use App\Service\SaveLogService;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SaveLogServiceTest extends TestCase
{
    private SaveLogService $service;

    private LogIteratorInterface&MockObject $iterator;

    protected function setUp(): void
    {
        $repository = $this->createMock(LogRepositoryInterface::class);
        $factory = $this->createMock(LogFactoryInterface::class);
        $lineParser = $this->createMock(LineParserInterface::class);
        $this->iterator = $this->createMock(LogIteratorInterface::class);

        $this->service = new SaveLogService(
            $repository,
            $factory,
            $lineParser,
            $this->iterator
        );
    }

    /**
     * @param array<mixed> $values
     */
    private function logLineGenerator(array $values): Generator
    {
        foreach ($values as $value) {
            yield $value;
        }
    }

    public function testFailIfLineInvalidException(): void
    {
        $this->expectException(LineInvalidServiceException::class);

        $this->iterator
            ->method('getLines')
            ->willReturn(
                $this->logLineGenerator([5, 6, 7, 8])
            );
        $this->service->save();
    }

    public function testSuccess(): void
    {
        $this->expectNotToPerformAssertions();

        $this->iterator
            ->method('getLines')
            ->willReturn(
                $this->logLineGenerator(['USER-SERVICE - - [18/Aug/2018:10:33:59 +0000] "POST /users HTTP/1.1" 201'])
            );

        $this->service->save();
    }
}
