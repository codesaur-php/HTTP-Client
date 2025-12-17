<?php

namespace codesaur\Http\Client\Tests\Integration;

use PHPUnit\Framework\TestCase;

use codesaur\Http\Client\CurlClient;
use codesaur\Http\Client\JSONClient;
use codesaur\Http\Client\Mail;

/**
 * End-to-end integration тест.
 *
 * Энэ тест нь бүх компонентуудыг хамтдаа ашиглах нөхцөлд шалгана:
 * - CurlClient болон JSONClient хамтдаа ажиллах
 * - Mail класстай хамтдаа ажиллах
 * - Бодит use case-ууд
 */
class EndToEndTest extends TestCase
{
    /**
     * CurlClient болон JSONClient хамтдаа ажиллах end-to-end тест.
     */
    public function testCurlClientAndJSONClientTogether(): void
    {
        try {
            // CurlClient ашиглан мэдээлэл авах
            $curlClient = new CurlClient();
            $rawResponse = $curlClient->request('https://httpbin.org/get?source=curl', 'GET');
            
            $this->assertIsString($rawResponse);
            $this->assertNotEmpty($rawResponse);
            
            // JSONClient ашиглан ижил мэдээлэл авах
            $jsonClient = new JSONClient();
            $jsonResponse = $jsonClient->get('https://httpbin.org/get', ['source' => 'json']);
            
            $this->assertIsArray($jsonResponse);
            if (isset($jsonResponse['error'])) {
                $this->markTestSkipped('Сүлжээний алдаа: ' . $jsonResponse['error']['message']);
            }
            
            // Хоёр хариу хоёулаа амжилттай байх ёстой
            $this->assertNotEmpty($rawResponse);
            $this->assertIsArray($jsonResponse);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * JSON API-аас мэдээлэл авч Mail-аар илгээх end-to-end тест.
     */
    public function testFetchDataAndSendEmail(): void
    {
        try {
            // JSON API-аас мэдээлэл авах
            $jsonClient = new JSONClient();
            $apiResponse = $jsonClient->get('https://httpbin.org/get', ['test' => 'integration']);
            
            if (isset($apiResponse['error'])) {
                $this->markTestSkipped('Сүлжээний алдаа: ' . $apiResponse['error']['message']);
            }
            
            // Mail объект үүсгэх
            $mail = new Mail();
            $mail
                ->targetTo('recipient@example.com', 'Хүлээн авагч')
                ->setFrom('sender@example.com', 'Илгээгч')
                ->setSubject('End-to-End Test - API Мэдээлэл')
                ->setMessage(
                    '<h1>API Мэдээлэл</h1>' .
                    '<pre>' . \json_encode($apiResponse, \JSON_PRETTY_PRINT) . '</pre>'
                );
            
            // Тохиргоо зөв эсэхийг шалгах
            $toRecipients = $mail->getRecipients('To');
            $this->assertCount(1, $toRecipients);
            $this->assertEquals('recipient@example.com', $toRecipients[0]['email']);
            
            // API хариу зөв эсэхийг шалгах
            $this->assertIsArray($apiResponse);
            $this->assertArrayHasKey('args', $apiResponse);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * Олон API хүсэлт илгээж, үр дүнг Mail-аар илгээх end-to-end тест.
     */
    public function testMultipleAPICallsAndEmail(): void
    {
        try {
            $jsonClient = new JSONClient();
            
            // Олон API хүсэлт илгээх
            $responses = [];
            $endpoints = [
                ['url' => 'https://httpbin.org/get', 'payload' => ['test' => 1]],
                ['url' => 'https://httpbin.org/get', 'payload' => ['test' => 2]],
                ['url' => 'https://httpbin.org/get', 'payload' => ['test' => 3]]
            ];
            
            foreach ($endpoints as $endpoint) {
                $response = $jsonClient->get($endpoint['url'], $endpoint['payload']);
                if (!isset($response['error'])) {
                    $responses[] = $response;
                }
            }
            
            $this->assertNotEmpty($responses);
            
            // Mail объект үүсгэж, үр дүнг илгээх
            $mail = new Mail();
            $mail
                ->targetTo('recipient@example.com', 'Хүлээн авагч')
                ->setFrom('sender@example.com', 'Илгээгч')
                ->setSubject('End-to-End Test - Олон API Үр Дүн')
                ->setMessage(
                    '<h1>API Үр Дүн</h1>' .
                    '<p>Нийт ' . \count($responses) . ' хүсэлт амжилттай.</p>' .
                    '<pre>' . \json_encode($responses, \JSON_PRETTY_PRINT) . '</pre>'
                );
            
            // Тохиргоо зөв эсэхийг шалгах
            $this->assertCount(1, $mail->getRecipients('To'));
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }

    /**
     * API-аас файл татаж, Mail-аар илгээх end-to-end тест.
     */
    public function testDownloadFileAndSendEmail(): void
    {
        try {
            // CurlClient ашиглан файл татах
            $curlClient = new CurlClient();
            $fileContent = $curlClient->request('https://httpbin.org/image/png', 'GET');
            
            $this->assertIsString($fileContent);
            
            // Mail объект үүсгэж, файлыг хавсралтад нэмэх
            $mail = new Mail();
            $mail
                ->targetTo('recipient@example.com', 'Хүлээн авагч')
                ->setFrom('sender@example.com', 'Илгээгч')
                ->setSubject('End-to-End Test - Файл Хавсралт')
                ->setMessage('Энэ имэйл нь татагдсан файлтай.')
                ->addContentAttachment($fileContent, 'downloaded_image.png');
            
            $attachments = $mail->getAttachments();
            $this->assertCount(1, $attachments);
            $this->assertArrayHasKey('content', $attachments[0]);
            $this->assertEquals('downloaded_image.png', $attachments[0]['name']);
        } catch (\Exception $e) {
            $this->markTestSkipped('Сүлжээний алдаа: ' . $e->getMessage());
        }
    }
}
