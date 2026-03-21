<?php

namespace codesaur\Http\Client\Tests;

use PHPUnit\Framework\TestCase;

use codesaur\Http\Client\CurlClient;
use codesaur\Http\Client\Response;

/**
 * CurlClient классын unit тест.
 *
 * Энэ тест нь CurlClient классын үндсэн функцүүдийг шалгана:
 * - GET, POST, PUT, DELETE хүсэлтүүд
 * - send() функц (Response обьект буцаах)
 * - sendWithRetry() дахин оролдох функц
 * - upload() файл илгээх функц
 * - debug горим
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

    // --- send() функцийн тестүүд ---

    /**
     * send() функц Response обьект буцаах тест.
     */
    public function testSendReturnsResponse(): void
    {
        try {
            $response = $this->client->send('https://httpbin.org/get');

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(200, $response->statusCode);
            $this->assertNotEmpty($response->body);
            $this->assertIsArray($response->headers);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * send() функц headers буцаах тест.
     */
    public function testSendReturnsHeaders(): void
    {
        try {
            $response = $this->client->send('https://httpbin.org/get');

            $this->assertNotEmpty($response->headers);
            $contentType = $response->getHeader('Content-Type');
            if ($contentType === null) {
                $this->markTestSkipped('Сүлжээний алдаа');
            }
            $this->assertStringContainsString('application/json', $contentType);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * send() POST хүсэлтэд Response обьект буцаах тест.
     */
    public function testSendPostRequest(): void
    {
        try {
            $response = $this->client->send(
                'https://httpbin.org/post',
                'POST',
                '{"test": "data"}'
            );

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(200, $response->statusCode);
            $this->assertTrue($response->isOk());
            $this->assertFalse($response->isError());

            $json = $response->json();
            $this->assertIsArray($json);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * send() 404 статус код буцаах тест.
     */
    public function testSendReturns404(): void
    {
        try {
            $response = $this->client->send('https://httpbin.org/status/404');

            $this->assertEquals(404, $response->statusCode);
            $this->assertFalse($response->isOk());
            $this->assertTrue($response->isError());
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * send() буруу URL-д Exception шидэх тест.
     */
    public function testSendThrowsOnInvalidUrl(): void
    {
        $this->expectException(\Exception::class);

        $this->client->send('https://invalid-domain-that-does-not-exist-12345.com');
    }

    // --- Debug горимын тестүүд ---

    /**
     * Debug горим идэвхжүүлэх тест.
     */
    public function testEnableDebug(): void
    {
        $result = $this->client->enableDebug(true);

        // Fluent interface шалгах
        $this->assertSame($this->client, $result);
    }

    /**
     * Debug лог бичигдэх тест.
     */
    public function testDebugLogRecordsRequests(): void
    {
        try {
            $this->client->enableDebug(true);
            $this->client->send('https://httpbin.org/get');

            $log = $this->client->getDebugLog();
            $this->assertCount(1, $log);
            $this->assertEquals('https://httpbin.org/get', $log[0]['uri']);
            $this->assertEquals('GET', $log[0]['method']);
            $this->assertEquals(200, $log[0]['status']);
            $this->assertArrayHasKey('response_headers', $log[0]);
            $this->assertArrayHasKey('response_body', $log[0]);
            $this->assertArrayHasKey('curl_info', $log[0]);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * Debug унтраалттай үед лог бичигдэхгүй тест.
     */
    public function testDebugLogNotRecordedWhenDisabled(): void
    {
        try {
            $this->client->send('https://httpbin.org/get');

            $this->assertEmpty($this->client->getDebugLog());
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * Debug лог цэвэрлэх тест.
     */
    public function testClearDebugLog(): void
    {
        try {
            $this->client->enableDebug(true);
            $this->client->send('https://httpbin.org/get');
            $this->assertNotEmpty($this->client->getDebugLog());

            $result = $this->client->clearDebugLog();
            $this->assertSame($this->client, $result);
            $this->assertEmpty($this->client->getDebugLog());
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    // --- sendWithRetry() тестүүд ---

    /**
     * sendWithRetry() амжилттай хүсэлт тест.
     */
    public function testSendWithRetrySuccess(): void
    {
        try {
            $response = $this->client->sendWithRetry('https://httpbin.org/get');

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(200, $response->statusCode);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * sendWithRetry() клиент алдааг дахин оролдохгүй тест.
     */
    public function testSendWithRetryDoesNotRetryClientErrors(): void
    {
        try {
            $response = $this->client->sendWithRetry(
                'https://httpbin.org/status/404',
                'GET',
                '',
                [],
                2,
                100
            );

            // 404 клиент алдаа - дахин оролдохгүй, шууд буцаана
            $this->assertEquals(404, $response->statusCode);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * sendWithRetry() буруу URL-д Exception шидэх тест.
     */
    public function testSendWithRetryThrowsAfterAllAttempts(): void
    {
        $this->expectException(\Exception::class);

        $this->client->sendWithRetry(
            'https://invalid-domain-that-does-not-exist-12345.com',
            'GET',
            '',
            [],
            1,
            100
        );
    }

    // --- upload() тестүүд ---

    /**
     * upload() файл олдохгүй бол Exception шидэх тест.
     */
    public function testUploadFileNotFound(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File not found');

        $this->client->upload(
            'https://httpbin.org/post',
            '/nonexistent/file.txt'
        );
    }

    /**
     * upload() файл илгээх тест.
     */
    public function testUploadFile(): void
    {
        // Түр файл үүсгэх
        $tempFile = \tempnam(\sys_get_temp_dir(), 'test_upload_');
        \file_put_contents($tempFile, 'test file content');

        try {
            $response = $this->client->upload(
                'https://httpbin.org/post',
                $tempFile,
                'document'
            );

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(200, $response->statusCode);

            $json = $response->json();
            if ($json === null) {
                $this->markTestSkipped('Сүлжээний алдаа');
            }
            $this->assertArrayHasKey('files', $json);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        } finally {
            \unlink($tempFile);
        }
    }

    /**
     * upload() нэмэлт field-тэй файл илгээх тест.
     */
    public function testUploadFileWithFields(): void
    {
        $tempFile = \tempnam(\sys_get_temp_dir(), 'test_upload_');
        \file_put_contents($tempFile, 'test content');

        try {
            $response = $this->client->upload(
                'https://httpbin.org/post',
                $tempFile,
                'file',
                ['description' => 'Test upload', 'category' => 'documents']
            );

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(200, $response->statusCode);

            $json = $response->json();
            if ($json === null) {
                $this->markTestSkipped('Сүлжээний алдаа');
            }
            $this->assertArrayHasKey('form', $json);
            $this->assertEquals('Test upload', $json['form']['description']);
            $this->assertEquals('documents', $json['form']['category']);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        } finally {
            \unlink($tempFile);
        }
    }
}
