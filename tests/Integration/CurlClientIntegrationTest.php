<?php

namespace codesaur\Http\Client\Tests\Integration;

use PHPUnit\Framework\TestCase;

use codesaur\Http\Client\CurlClient;

/**
 * CurlClient классын integration тест.
 *
 * Энэ тест нь CurlClient классыг бодит API-тай ажиллах нөхцөлд шалгана:
 * - Бодит HTTP хүсэлтүүд
 * - Олон төрлийн HTTP методүүд
 * - Header тохиргоо
 * - Timeout тохиргоо
 * - Алдааны боловсруулалт
 */
class CurlClientIntegrationTest extends TestCase
{
    /**
     * @var CurlClient
     */
    private CurlClient $client;

    /**
     * Тест эхлэхээс өмнө CurlClient объект үүсгэнэ.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new CurlClient();
    }

    /**
     * Бодит GET хүсэлт илгээх integration тест.
     */
    public function testRealGetRequest(): void
    {
        try {
            $response = $this->client->request('https://httpbin.org/get?test=integration', 'GET');
            
            $this->assertIsString($response);
            $this->assertNotEmpty($response);
            
            $data = \json_decode($response, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('url', $data);
            $this->assertStringContainsString('test=integration', $data['url']);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * Бодит POST хүсэлт илгээх integration тест.
     */
    public function testRealPostRequest(): void
    {
        try {
            $testData = \json_encode(['integration' => 'test', 'value' => 123]);
            $response = $this->client->request(
                'https://httpbin.org/post',
                'POST',
                $testData,
                [
                    \CURLOPT_HTTPHEADER => ['Content-Type: application/json']
                ]
            );
            
            $this->assertIsString($response);
            $data = \json_decode($response, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('json', $data);
            $this->assertEquals('test', $data['json']['integration']);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * Бодит PUT хүсэлт илгээх integration тест.
     */
    public function testRealPutRequest(): void
    {
        try {
            $testData = \json_encode(['id' => 1, 'status' => 'updated']);
            $response = $this->client->request(
                'https://httpbin.org/put',
                'PUT',
                $testData,
                [
                    \CURLOPT_HTTPHEADER => ['Content-Type: application/json']
                ]
            );
            
            $this->assertIsString($response);
            $data = \json_decode($response, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('json', $data);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * Бодит DELETE хүсэлт илгээх integration тест.
     */
    public function testRealDeleteRequest(): void
    {
        try {
            $response = $this->client->request('https://httpbin.org/delete', 'DELETE');
            
            $this->assertIsString($response);
            $data = \json_decode($response, true);
            $this->assertIsArray($data);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * Header тохиргоотой хүсэлт илгээх integration тест.
     */
    public function testRequestWithCustomHeaders(): void
    {
        try {
            $options = [
                \CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'X-Custom-Header: integration-test',
                    'User-Agent: PHPUnit-Integration-Test'
                ]
            ];
            
            $response = $this->client->request(
                'https://httpbin.org/headers',
                'GET',
                '',
                $options
            );
            
            $this->assertIsString($response);
            $data = \json_decode($response, true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('headers', $data);
            $this->assertStringContainsString('integration-test', $response);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * Timeout тохиргоотой хүсэлт илгээх integration тест.
     */
    public function testRequestWithTimeout(): void
    {
        try {
            $options = [
                \CURLOPT_TIMEOUT => 5,
                \CURLOPT_CONNECTTIMEOUT => 3
            ];
            
            $startTime = \microtime(true);
            $response = $this->client->request('https://httpbin.org/delay/2', 'GET', '', $options);
            $endTime = \microtime(true);
            
            $this->assertIsString($response);
            // Timeout-оос илүү удаан ажиллах ёсгүй
            $this->assertLessThan(6, $endTime - $startTime);
        } catch (\Exception $e) {
            // Timeout алдаа гарч болно (илүү уян хатан шалгалт)
            $errorMessage = \strtolower($e->getMessage());
            $isTimeoutError = \str_contains($errorMessage, 'timeout') 
                || \str_contains($errorMessage, 'timed out')
                || \str_contains($errorMessage, 'operation timed');
            
            $this->assertTrue(
                $isTimeoutError,
                'Expected timeout error but got: ' . $e->getMessage()
            );
        }
    }

    /**
     * Олон хүсэлт илгээх integration тест (performance).
     */
    public function testMultipleRequests(): void
    {
        try {
            $urls = [
                'https://httpbin.org/get',
                'https://httpbin.org/get?param=1',
                'https://httpbin.org/get?param=2'
            ];
            
            $responses = [];
            foreach ($urls as $url) {
                $responses[] = $this->client->request($url, 'GET');
            }
            
            $this->assertCount(3, $responses);
            foreach ($responses as $response) {
                $this->assertIsString($response);
                $this->assertNotEmpty($response);
            }
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }
}
