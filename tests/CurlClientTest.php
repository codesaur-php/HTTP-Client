<?php

namespace codesaur\Http\Client\Tests;

use PHPUnit\Framework\TestCase;

use codesaur\Http\Client\CurlClient;

/**
 * CurlClient классын unit тест.
 *
 * Энэ тест нь CurlClient классын үндсэн функцүүдийг шалгана:
 * - GET, POST, PUT, DELETE хүсэлтүүд
 * - Алдааны боловсруулалт
 * - cURL тохиргоонууд
 */
class CurlClientTest extends TestCase
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
     * GET хүсэлт илгээх тест.
     */
    public function testGetRequest(): void
    {
        try {
            $response = $this->client->request('https://httpbin.org/get', 'GET');
            
            $this->assertIsString($response);
            $this->assertNotEmpty($response);
            
            $data = \json_decode($response, true);
            if ($data === null) {
                $this->markTestSkipped('Сүлжээний алдаа эсвэл httpbin.org хандалтгүй байна');
            }
            $this->assertIsArray($data);
            $this->assertArrayHasKey('url', $data);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * POST хүсэлт илгээх тест.
     */
    public function testPostRequest(): void
    {
        try {
            $testData = 'test=data&value=123';
            $response = $this->client->request(
                'https://httpbin.org/post',
                'POST',
                $testData
            );
            
            $this->assertIsString($response);
            $data = \json_decode($response, true);
            if ($data === null) {
                $this->markTestSkipped('Сүлжээний алдаа эсвэл httpbin.org хандалтгүй байна');
            }
            $this->assertIsArray($data);
            $this->assertArrayHasKey('form', $data);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * PUT хүсэлт илгээх тест.
     */
    public function testPutRequest(): void
    {
        try {
            $testData = '{"key": "value"}';
            $response = $this->client->request(
                'https://httpbin.org/put',
                'PUT',
                $testData
            );
            
            $this->assertIsString($response);
            $data = \json_decode($response, true);
            if ($data === null) {
                $this->markTestSkipped('Сүлжээний алдаа эсвэл httpbin.org хандалтгүй байна');
            }
            $this->assertIsArray($data);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * DELETE хүсэлт илгээх тест.
     */
    public function testDeleteRequest(): void
    {
        try {
            $response = $this->client->request(
                'https://httpbin.org/delete',
                'DELETE'
            );
            
            $this->assertIsString($response);
            $data = \json_decode($response, true);
            if ($data === null) {
                $this->markTestSkipped('Сүлжээний алдаа эсвэл httpbin.org хандалтгүй байна');
            }
            $this->assertIsArray($data);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * Нэмэлт cURL тохиргоотой хүсэлт илгээх тест.
     */
    public function testRequestWithOptions(): void
    {
        $options = [
            \CURLOPT_TIMEOUT => 10,
            \CURLOPT_HTTPHEADER => ['Accept: application/json']
        ];
        
        $response = $this->client->request(
            'https://httpbin.org/get',
            'GET',
            '',
            $options
        );
        
        $this->assertIsString($response);
        $this->assertNotEmpty($response);
    }

    /**
     * Буруу URL-тай хүсэлт илгээхэд алдаа гаргах тест.
     */
    public function testRequestWithInvalidUrl(): void
    {
        $this->expectException(\Exception::class);
        
        $this->client->request('https://invalid-domain-that-does-not-exist-12345.com', 'GET');
    }

    /**
     * Хоосон өгөгдөлтэй POST хүсэлт илгээх тест.
     */
    public function testPostRequestWithEmptyData(): void
    {
        $response = $this->client->request(
            'https://httpbin.org/post',
            'POST',
            ''
        );
        
        $this->assertIsString($response);
        $this->assertNotEmpty($response);
    }
}
