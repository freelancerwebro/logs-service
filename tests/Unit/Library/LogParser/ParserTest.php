<?php

declare(strict_types=1);

namespace App\Tests\Unit\Library\LogParser;

use App\Library\LogParser\Parser;
use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    private const PATTERN = '/(?<serviceName>\S+)\s+-\s+-\s+\[(?<date>.+)\]\s+"(?<method>\S+)\s+(?<path>\S+)\s+(?<http>[0-9A-Z\/.]+)"\s+(?<responseCode>\d+)/';

    /**
     * @param array<mixed>[] $expectedResult
     *
     * @dataProvider getTestParseLineData()
     */
    public function testParseLine(string $pattern, string $line, array $expectedResult): void
    {
        $parser = new Parser(self::PATTERN);
        $result = $parser->parseLine($line);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array<mixed>[]
     */
    public function getTestParseLineData(): array
    {
        return [
            [
                self::PATTERN,
                'USER-SERVICE - - [17/Aug/2018:09:21:53 +0000] "POST /users HTTP/1.1" 201',
                [
                    'method' => 'POST',
                    'path' => '/users',
                    'responseCode' => '201',
                    'serviceName' => 'USER-SERVICE',
                    'date' => '17/Aug/2018:09:21:53 +0000',
                    'http' => 'HTTP/1.1',
                ],
            ],
            [
                self::PATTERN,
                'INVOICE-SERVICE - - [17/Aug/2018:09:22:59 +0000] "POST /invoices HTTP/1.1" 400',
                [
                    'method' => 'POST',
                    'path' => '/invoices',
                    'responseCode' => '400',
                    'serviceName' => 'INVOICE-SERVICE',
                    'date' => '17/Aug/2018:09:22:59 +0000',
                    'http' => 'HTTP/1.1',
                ],
            ],
            [
                self::PATTERN,
                'wrong log line',
                [],
            ],
            [
                'wrong pattern',
                'wrong log line',
                [],
            ],
        ];
    }
}
