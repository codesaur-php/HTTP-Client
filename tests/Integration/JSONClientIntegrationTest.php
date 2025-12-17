<?php

namespace codesaur\Http\Client\Tests\Integration;

use PHPUnit\Framework\TestCase;

use codesaur\Http\Client\JSONClient;

/**
 * JSONClient классын integration тест.
 *
 * Энэ тест нь JSONClient классыг бодит API-тай ажиллах нөхцөлд шалгана:
 * - Бодит JSON API хүсэлтүүд
 * - SSL verify тохиргоо (development/production)
 * - Header тохиргоо
 * - Алдааны боловсруулалт
 * - Олон төрлийн өгөгдөл
 */
class JSONClientIntegrationTest extends TestCase
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
     * Бодит JSON GET хүсэлт илгээх integration тест.
     */
    public function testRealGetRequest(): void
    {
        $response = $this->client->get(
            'https://httpbin.org/get',
            ['integration' => 'test', 'number' => 42]
        );
        
        $this->assertIsArray($response);
        if (isset($response['error'])) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $response['error']['message']);
        }
        
        $this->assertArrayHasKey('args', $response);
        $this->assertEquals('test', $response['args']['integration']);
        $this->assertEquals('42', $response['args']['number']);
    }

    /**
     * Бодит JSON POST хүсэлт илгээх integration тест.
     */
    public function testRealPostRequest(): void
    {
        $payload = [
            'name' => 'Integration Test',
            'type' => 'json',
            'data' => ['key' => 'value']
        ];
        
        $response = $this->client->post(
            'https://httpbin.org/post',
            $payload
        );
        
        $this->assertIsArray($response);
        if (isset($response['error'])) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $response['error']['message']);
        }
        
        $this->assertArrayHasKey('json', $response);
        $this->assertEquals('Integration Test', $response['json']['name']);
        $this->assertEquals('json', $response['json']['type']);
    }

    /**
     * Бодит JSON PUT хүсэлт илгээх integration тест.
     */
    public function testRealPutRequest(): void
    {
        $payload = [
            'id' => 1,
            'status' => 'updated',
            'timestamp' => \time()
        ];
        
        $response = $this->client->put(
            'https://httpbin.org/put',
            $payload
        );
        
        $this->assertIsArray($response);
        if (isset($response['error'])) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $response['error']['message']);
        }
        
        $this->assertArrayHasKey('json', $response);
        $this->assertEquals(1, $response['json']['id']);
    }

    /**
     * Бодит JSON DELETE хүсэлт илгээх integration тест.
     */
    public function testRealDeleteRequest(): void
    {
        $payload = ['id' => 123, 'reason' => 'integration-test'];
        
        $response = $this->client->delete(
            'https://httpbin.org/delete',
            $payload
        );
        
        $this->assertIsArray($response);
        if (isset($response['error'])) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $response['error']['message']);
        }
    }

    /**
     * Header тохиргоотой JSON хүсэлт илгээх integration тест.
     */
    public function testRequestWithCustomHeaders(): void
    {
        $headers = [
            'X-Integration-Test' => 'true',
            'Authorization' => 'Bearer test-token-123',
            'X-Custom-Header' => 'integration-value'
        ];
        
        $response = $this->client->get(
            'https://httpbin.org/headers',
            [],
            $headers
        );
        
        $this->assertIsArray($response);
        if (isset($response['error'])) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $response['error']['message']);
        }
        
        $this->assertArrayHasKey('headers', $response);
        $responseString = \json_encode($response);
        $this->assertStringContainsString('integration-test', \strtolower($responseString));
    }

    /**
     * Олон төрлийн өгөгдөлтэй JSON POST хүсэлт integration тест.
     */
    public function testPostWithComplexData(): void
    {
        $payload = [
            'string' => 'test',
            'number' => 42,
            'float' => 3.14,
            'boolean' => true,
            'null' => null,
            'array' => [1, 2, 3],
            'object' => [
                'nested' => 'value',
                'deep' => [
                    'level' => 2
                ]
            ]
        ];
        
        $response = $this->client->post(
            'https://httpbin.org/post',
            $payload
        );
        
        $this->assertIsArray($response);
        if (isset($response['error'])) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $response['error']['message']);
        }
        
        $this->assertArrayHasKey('json', $response);
        $this->assertEquals($payload, $response['json']);
    }

    /**
     * Буруу URL-тай хүсэлт илгээхэд алдаа буцаах integration тест.
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
     * Олон хүсэлт илгээх integration тест (performance).
     */
    public function testMultipleRequests(): void
    {
        $requests = [
            ['method' => 'get', 'payload' => ['test' => 1]],
            ['method' => 'get', 'payload' => ['test' => 2]],
            ['method' => 'get', 'payload' => ['test' => 3]]
        ];
        
        $responses = [];
        foreach ($requests as $request) {
            $responses[] = $this->client->get(
                'https://httpbin.org/get',
                $request['payload']
            );
        }
        
        $this->assertCount(3, $responses);
        foreach ($responses as $response) {
            $this->assertIsArray($response);
            if (isset($response['error'])) {
                $this->markTestSkipped('Сүлжээний алдаа: ' . $response['error']['message']);
            }
        }
    }

    /**
     * SSL verify тохиргоо integration тест (development орчинд).
     */
    public function testSSLVerificationInDevelopment(): void
    {
        // Development орчинд тохируулах
        \putenv('CODESAUR_APP_ENV=development');
        
        // Шинэ client үүсгэх (environment variable унших)
        $devClient = new JSONClient();
        
        $response = $devClient->get('https://httpbin.org/get', []);
        
        $this->assertIsArray($response);
        // Development орчинд SSL verify унтраалттай тул амжилттай эсвэл алдаа гарч болно
        // Гэхдээ алдааны бүтэц зөв байх ёстой
        if (isset($response['error'])) {
            $this->assertArrayHasKey('code', $response['error']);
            $this->assertArrayHasKey('message', $response['error']);
        }
        
        // Environment variable устгах
        \putenv('CODESAUR_APP_ENV');
    }
}
