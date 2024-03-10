<?php

declare(strict_types=1);

namespace App\Tests\Application\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

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

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson((string) $response->getContent());

        $this->assertJsonStringEqualsJsonString(
            '{"counter":3}',
            (string) $response->getContent()
        );
    }

    public function testCountWithFiltersSuccess(): void
    {
        $client = static::createClient();
        $client->request(
            self::COUNT_METHOD,
            self::COUNT_ENDPOINT.'?statusCode=200&serviceNames[]=AUTH-SERVICE&serviceNames[]=INVOICE-SERVICE&startDate=2024-02-01 00:00:00&endDate=2024-03-09 23:59:59'
        );
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson((string) $response->getContent());

        $this->assertJsonStringEqualsJsonString(
            '{"counter":2}',
            (string) $response->getContent()
        );
    }

    public function testDeleteSuccess(): void
    {
        $client = static::createClient();
        $client->request(
            self::TRUNCATE_METHOD,
            self::TRUNCATE_ENDPOINT
        );
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $client->request(
            self::COUNT_METHOD,
            self::COUNT_ENDPOINT
        );
        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString(
            '{"counter":0}',
            (string) $response->getContent()
        );
    }

    public function testCountFailIfWrongStatusCode(): void
    {
        $client = static::createClient();
        $client->request(
            self::COUNT_METHOD,
            self::COUNT_ENDPOINT.'?statusCode=213123123123'
        );
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJson((string) $response->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"StatusCode is not a valid HTTP code"}',
            (string) $response->getContent()
        );
    }

    public function testCountFailIfWrongServiceName(): void
    {
        $client = static::createClient();
        $client->request(
            self::COUNT_METHOD,
            self::COUNT_ENDPOINT.'?serviceNames[]=wrong1'
        );
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJson((string) $response->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"ServiceName is invalid"}',
            (string) $response->getContent()
        );
    }

    public function testCountFailIfWrongStartDate(): void
    {
        $client = static::createClient();
        $client->request(
            self::COUNT_METHOD,
            self::COUNT_ENDPOINT.'?startDate=wrongDate'
        );
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJson((string) $response->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"StartDate is not a valid datetime"}',
            (string) $response->getContent()
        );
    }

    public function testCountFailIfWrongEndDate(): void
    {
        $client = static::createClient();
        $client->request(
            self::COUNT_METHOD,
            self::COUNT_ENDPOINT.'?endDate=wrongDate'
        );
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJson((string) $response->getContent());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"EndDate is not a valid datetime"}',
            (string) $response->getContent()
        );
    }
}
