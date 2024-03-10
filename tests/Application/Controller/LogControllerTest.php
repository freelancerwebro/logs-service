<?php

declare(strict_types=1);

namespace App\Tests\Application\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LogControllerTest extends WebTestCase
{
    private const COUNT_ENDPOINT = '/count';
    private const COUNT_METHOD = 'GET';
    private const TRUNCATE_ENDPOINT = '/delete';
    private const TRUNCATE_METHOD = 'DELETE';

    public function testCountSuccess(): void
    {
        $client = static::createClient();
        $client->request(self::COUNT_METHOD, self::COUNT_ENDPOINT);
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $this->assertJsonStringEqualsJsonString(
            '{"counter":3}',
            $response->getContent()
        );
    }

    public function testCountWithFiltersSuccess(): void
    {
        $client = static::createClient();
        $client->request(
            self::COUNT_METHOD,
            self::COUNT_ENDPOINT . '?statusCode=200&serviceNames[]=AUTH-SERVICE&serviceNames[]=INVOICE-SERVICE&startDate=2024-02-01 00:00:00&endDate=2024-03-09 23:59:59'
        );
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $this->assertJsonStringEqualsJsonString(
            '{"counter":2}',
            $response->getContent()
        );
    }

    public function testDeleteSuccess(): void
    {
        $client = static::createClient();
        $client->request(self::TRUNCATE_METHOD, self::TRUNCATE_ENDPOINT);
        $response = $client->getResponse();

        $this->assertEquals(204, $response->getStatusCode());

        $client->request('GET', '/log/count');
        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString(
            '{"counter":0}',
            $response->getContent()
        );
    }

    public function testCountFailIfWrongStatusCode(): void
    {
        $client = static::createClient();
        $client->request(
            self::COUNT_METHOD,
            self::COUNT_ENDPOINT . '?statusCode=213123123123'
        );
        $response = $client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"StatusCode is not a valid HTTP code"}',
            $response->getContent()
        );
    }

    public function testCountFailIfWrongServiceName(): void
    {
        $client = static::createClient();
        $client->request(
            self::COUNT_METHOD,
            self::COUNT_ENDPOINT . '?serviceNames[]=wrong1'
        );
        $response = $client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"ServiceName is invalid"}',
            $response->getContent()
        );
    }

    public function testCountFailIfWrongStartDate(): void
    {
        $client = static::createClient();
        $client->request(
            self::COUNT_METHOD,
            self::COUNT_ENDPOINT . '?startDate=wrongDate'
        );
        $response = $client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"StartDate is not a valid datetime"}',
            $response->getContent()
        );
    }

    public function testCountFailIfWrongEndDate(): void
    {
        $client = static::createClient();
        $client->request(
            self::COUNT_METHOD,
            self::COUNT_ENDPOINT . '?endDate=wrongDate'
        );
        $response = $client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"EndDate is not a valid datetime"}',
            $response->getContent()
        );
    }
}
