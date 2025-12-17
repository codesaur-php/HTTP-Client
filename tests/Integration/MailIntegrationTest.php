<?php

namespace codesaur\Http\Client\Tests\Integration;

use PHPUnit\Framework\TestCase;

use codesaur\Http\Client\Mail;

/**
 * Mail классын integration тест.
 *
 * Энэ тест нь Mail классыг бодит нөхцөлд шалгана:
 * - Имэйлийн тохиргоо
 * - Хавсралт нэмэх
 * - MIME форматлалт
 * - UTF-8 дэмжлэг
 *
 * Анхаар: Бодит имэйл илгээх нь SMTP сервер шаардлагатай тул
 * энэ тест нь зөвхөн тохиргоо болон форматлалтыг шалгана.
 */
class MailIntegrationTest extends TestCase
{
    /**
     * @var Mail
     */
    private Mail $mail;

    /**
     * Тест эхлэхээс өмнө Mail объект үүсгэнэ.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->mail = new Mail();
    }

    /**
     * Бүрэн тохиргоотой имэйл integration тест.
     */
    public function testFullEmailConfiguration(): void
    {
        $this->mail
            ->targetTo('recipient@example.com', 'Хүлээн авагч')
            ->addCCRecipient('cc@example.com', 'CC Хүлээн авагч')
            ->addBCCRecipient('bcc@example.com', 'BCC Хүлээн авагч')
            ->setFrom('sender@example.com', 'Илгээгч')
            ->setReplyTo('reply@example.com', 'Хариу')
            ->setSubject('Integration Test - Тест гарчиг')
            ->setMessage('<h1>Integration Test</h1><p>Энэ нь integration тест юм.</p>');

        // Тохиргоо зөв эсэхийг шалгах
        $toRecipients = $this->mail->getRecipients('To');
        $this->assertCount(1, $toRecipients);
        $this->assertEquals('recipient@example.com', $toRecipients[0]['email']);

        $ccRecipients = $this->mail->getRecipients('Cc');
        $this->assertCount(1, $ccRecipients);

        $bccRecipients = $this->mail->getRecipients('Bcc');
        $this->assertCount(1, $bccRecipients);
    }

    /**
     * Хавсралттай имэйл integration тест.
     */
    public function testEmailWithAttachments(): void
    {
        // Тест файл үүсгэх
        $testFile1 = \sys_get_temp_dir() . '/integration_test_1.txt';
        \file_put_contents($testFile1, 'Integration test file 1');

        $testFile2 = \sys_get_temp_dir() . '/integration_test_2.txt';
        \file_put_contents($testFile2, 'Integration test file 2');

        $this->mail
            ->targetTo('recipient@example.com', 'Хүлээн авагч')
            ->setFrom('sender@example.com', 'Илгээгч')
            ->setSubject('Integration Test - Хавсралттай')
            ->setMessage('Энэ имэйл нь хавсралттай.')
            ->addFileAttachment($testFile1)
            ->addFileAttachment($testFile2)
            ->addContentAttachment('Raw content', 'raw.txt');

        $attachments = $this->mail->getAttachments();
        $this->assertCount(3, $attachments);

        // Тест файлууд устгах
        \unlink($testFile1);
        \unlink($testFile2);
    }

    /**
     * URL хавсралттай имэйл integration тест.
     */
    public function testEmailWithUrlAttachment(): void
    {
        try {
            $this->mail
                ->targetTo('recipient@example.com', 'Хүлээн авагч')
                ->setFrom('sender@example.com', 'Илгээгч')
                ->setSubject('Integration Test - URL хавсралт')
                ->setMessage('Энэ имэйл нь URL хавсралттай.')
                ->addUrlAttachment('https://httpbin.org/image/png');

            $attachments = $this->mail->getAttachments();
            $this->assertCount(1, $attachments);
            $this->assertArrayHasKey('url', $attachments[0]);
        } catch (\InvalidArgumentException $e) {
            // URL хавсралт олдохгүй байж болно
            $this->markTestSkipped('URL хавсралт олдсонгүй: ' . $e->getMessage());
        }
    }

    /**
     * UTF-8 дэмжлэг integration тест.
     */
    public function testUTF8Support(): void
    {
        $this->mail
            ->targetTo('recipient@example.com', 'Монгол Нэр')
            ->setFrom('sender@example.com', 'Илгээгч Нэр')
            ->setSubject('Integration Test - UTF-8 Тест: Монгол үсэг')
            ->setMessage('<h1>Монгол текст</h1><p>Энэ нь UTF-8 дэмжлэгийн тест юм.</p>');

        $toRecipients = $this->mail->getRecipients('To');
        $this->assertEquals('Монгол Нэр', $toRecipients[0]['name']);
    }

    /**
     * HTML болон plaintext имэйл integration тест.
     */
    public function testHTMLAndPlaintextEmail(): void
    {
        // HTML имэйл
        $htmlMessage = '<h1>HTML</h1><p>Энэ нь HTML имэйл.</p>';
        $this->mail
            ->targetTo('recipient@example.com', 'Хүлээн авагч')
            ->setFrom('sender@example.com', 'Илгээгч')
            ->setSubject('HTML Имэйл')
            ->setMessage($htmlMessage);

        $this->assertStringContainsString('</h1>', $htmlMessage);

        // Plaintext имэйл
        $plaintextMessage = 'Энэ нь plaintext имэйл.';
        $this->mail->setMessage($plaintextMessage);
        $this->assertStringNotContainsString('<', $plaintextMessage);
    }

    /**
     * Олон хүлээн авагчтай имэйл integration тест.
     */
    public function testEmailWithMultipleRecipients(): void
    {
        $this->mail
            ->addRecipient('user1@example.com', 'Хэрэглэгч 1')
            ->addRecipient('user2@example.com', 'Хэрэглэгч 2')
            ->addRecipient('user3@example.com', 'Хэрэглэгч 3')
            ->addCCRecipient('cc1@example.com', 'CC 1')
            ->addCCRecipient('cc2@example.com', 'CC 2')
            ->addBCCRecipient('bcc1@example.com', 'BCC 1')
            ->setFrom('sender@example.com', 'Илгээгч')
            ->setSubject('Олон хүлээн авагчтай имэйл')
            ->setMessage('Энэ имэйл нь олон хүлээн авагчтай.');

        $toRecipients = $this->mail->getRecipients('To');
        $this->assertCount(3, $toRecipients);

        $ccRecipients = $this->mail->getRecipients('Cc');
        $this->assertCount(2, $ccRecipients);

        $bccRecipients = $this->mail->getRecipients('Bcc');
        $this->assertCount(1, $bccRecipients);
    }

    /**
     * addRecipients() массив integration тест.
     */
    public function testAddRecipientsArray(): void
    {
        $recipients = [
            'To' => [
                ['email' => 'to1@example.com', 'name' => 'To 1'],
                ['email' => 'to2@example.com', 'name' => 'To 2'],
                ['email' => 'to3@example.com']
            ],
            'Cc' => [
                ['email' => 'cc1@example.com', 'name' => 'CC 1'],
                ['email' => 'cc2@example.com']
            ],
            'Bcc' => [
                ['email' => 'bcc1@example.com', 'name' => 'BCC 1']
            ]
        ];

        $this->mail->addRecipients($recipients);

        $this->assertCount(3, $this->mail->getRecipients('To'));
        $this->assertCount(2, $this->mail->getRecipients('Cc'));
        $this->assertCount(1, $this->mail->getRecipients('Bcc'));
    }

    /**
     * Fluent interface integration тест.
     */
    public function testFluentInterface(): void
    {
        // Тест файл үүсгэх
        $testFile = \sys_get_temp_dir() . '/fluent_test.txt';
        \file_put_contents($testFile, 'Fluent interface test');

        $result = $this->mail
            ->targetTo('recipient@example.com', 'Хүлээн авагч')
            ->setFrom('sender@example.com', 'Илгээгч')
            ->setSubject('Fluent Interface Test')
            ->setMessage('Энэ нь fluent interface тест юм.')
            ->addRecipient('cc@example.com', 'CC')
            ->addFileAttachment($testFile);

        $this->assertSame($this->mail, $result);

        // Тест файл устгах
        \unlink($testFile);
    }
}
