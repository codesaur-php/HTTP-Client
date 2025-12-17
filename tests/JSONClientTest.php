<?php

namespace codesaur\Http\Client\Tests;

use PHPUnit\Framework\TestCase;

use codesaur\Http\Client\JSONClient;

/**
 * JSONClient классын unit тест.
 *
 * Энэ тест нь JSONClient классын функцүүдийг шалгана:
 * - GET, POST, PUT, DELETE хүсэлтүүд
 * - JSON encode/decode
 * - Алдааны боловсруулалт
 */
class JSONClientTest extends TestCase
{
    /**
     * @var JSONClient
     */
    private JSONClient $client;

    /**
     * Тест эхлэхээс өмнө JSONClient объект үүсгэнэ.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new JSONClient();
    }

    /**
     * GET хүсэлт илгээх тест.
     */
    public function testGetRequest(): void
    {
        $response = $this->client->get(
            'https://httpbin.org/get',
            ['test' => 'value', 'number' => 123]
        );
        
        $this->assertIsArray($response);
        // Сүлжээний алдаа гарч болзошгүй тул алдаа байвал skip хийх
        if (isset($response['error'])) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $response['error']['message']);
        }
        $this->assertArrayHasKey('args', $response);
    }

    /**
     * POST хүсэлт илгээх тест.
     */
    public function testPostRequest(): void
    {
        $payload = ['name' => 'codesaur', 'type' => 'test'];
        $response = $this->client->post(
            'https://httpbin.org/post',
            $payload
        );
        
        $this->assertIsArray($response);
        // Сүлжээний алдаа гарч болзошгүй тул алдаа байвал skip хийх
        if (isset($response['error'])) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $response['error']['message']);
        }
        $this->assertArrayHasKey('json', $response);
        $this->assertEquals('codesaur', $response['json']['name']);
    }

    /**
     * PUT хүсэлт илгээх тест.
     */
    public function testPutRequest(): void
    {
        $payload = ['id' => 1, 'status' => 'updated'];
        $response = $this->client->put(
            'https://httpbin.org/put',
            $payload
        );
        
        $this->assertIsArray($response);
        // Сүлжээний алдаа гарч болзошгүй тул алдаа байвал skip хийх
        if (isset($response['error'])) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $response['error']['message']);
        }
        $this->assertArrayHasKey('json', $response);
    }

    /**
     * DELETE хүсэлт илгээх тест.
     */
    public function testDeleteRequest(): void
    {
        $payload = ['id' => 123];
        $response = $this->client->delete(
            'https://httpbin.org/delete',
            $payload
        );
        
        $this->assertIsArray($response);
        // Сүлжээний алдаа гарч болзошгүй тул алдаа байвал skip хийх
        if (isset($response['error'])) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $response['error']['message']);
        }
    }

    /**
     * Нэмэлт header-тэй хүсэлт илгээх тест.
     */
    public function testRequestWithHeaders(): void
    {
        $headers = [
            'X-Custom-Header' => 'test-value',
            'Authorization' => 'Bearer token123'
        ];
        
        $response = $this->client->get(
            'https://httpbin.org/headers',
            [],
            $headers
        );
        
        $this->assertIsArray($response);
        // Сүлжээний алдаа гарч болзошгүй тул алдаа байвал skip хийх
        if (isset($response['error'])) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $response['error']['message']);
        }
        $this->assertArrayHasKey('headers', $response);
    }

    /**
     * Хоосон payload-тэй хүсэлт илгээх тест.
     */
    public function testRequestWithEmptyPayload(): void
    {
        $response = $this->client->get('https://httpbin.org/get', []);
        
        $this->assertIsArray($response);
        // Сүлжээний алдаа гарч болзошгүй тул алдаа байвал skip хийх
        if (isset($response['error'])) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $response['error']['message']);
        }
    }

    /**
     * Буруу URL-тай хүсэлт илгээхэд алдаа буцаах тест.
     */
    public function testRequestWithInvalidUrl(): void
    {
        $response = $this->client->get(
            'https://invalid-domain-that-does-not-exist-12345.com',
            []
        );
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertArrayHasKey('code', $response['error']);
        $this->assertArrayHasKey('message', $response['error']);
    }

    /**
     * Олон төрлийн өгөгдөлтэй POST хүсэлт илгээх тест.
     */
    public function testPostRequestWithComplexData(): void
    {
        $payload = [
            'string' => 'test',
            'number' => 42,
            'boolean' => true,
            'array' => [1, 2, 3],
            'object' => ['key' => 'value']
        ];
        
        $response = $this->client->post(
            'https://httpbin.org/post',
            $payload
        );
        
        $this->assertIsArray($response);
        // Сүлжээний алдаа гарч болзошгүй тул алдаа байвал skip хийх
        if (isset($response['error'])) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $response['error']['message']);
        }
        $this->assertEquals($payload, $response['json']);
    }
}
