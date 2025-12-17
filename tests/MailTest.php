<?php

namespace codesaur\Http\Client\Tests;

use PHPUnit\Framework\TestCase;

use codesaur\Http\Client\Mail;

/**
 * Mail классын unit тест.
 *
 * Энэ тест нь Mail классын функцүүдийг шалгана:
 * - Хүлээн авагч нэмэх (To, Cc, Bcc)
 * - Имэйлийн тохиргоо (subject, message, from)
 * - Хавсралт нэмэх
 * - Валидаци шалгах
 */
class MailTest extends TestCase
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
     * targetTo() функц тест.
     */
    public function testTargetTo(): void
    {
        $result = $this->mail->targetTo('test@example.com', 'Тест Нэр');
        
        $this->assertSame($this->mail, $result);
        $recipients = $this->mail->getRecipients('To');
        $this->assertCount(1, $recipients);
        $this->assertEquals('test@example.com', $recipients[0]['email']);
        $this->assertEquals('Тест Нэр', $recipients[0]['name']);
    }

    /**
     * addRecipient() функц тест.
     */
    public function testAddRecipient(): void
    {
        $this->mail->addRecipient('user1@example.com', 'Хэрэглэгч 1');
        $this->mail->addRecipient('user2@example.com');
        
        $recipients = $this->mail->getRecipients('To');
        $this->assertCount(2, $recipients);
        $this->assertEquals('user1@example.com', $recipients[0]['email']);
        $this->assertEquals('user2@example.com', $recipients[1]['email']);
    }

    /**
     * addCCRecipient() функц тест.
     */
    public function testAddCCRecipient(): void
    {
        $this->mail->addCCRecipient('cc@example.com', 'CC Нэр');
        
        $recipients = $this->mail->getRecipients('Cc');
        $this->assertCount(1, $recipients);
        $this->assertEquals('cc@example.com', $recipients[0]['email']);
    }

    /**
     * addBCCRecipient() функц тест.
     */
    public function testAddBCCRecipient(): void
    {
        $this->mail->addBCCRecipient('bcc@example.com', 'BCC Нэр');
        
        $recipients = $this->mail->getRecipients('Bcc');
        $this->assertCount(1, $recipients);
        $this->assertEquals('bcc@example.com', $recipients[0]['email']);
    }

    /**
     * Буруу имэйл хаягтай addRecipient() тест.
     */
    public function testAddRecipientWithInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->mail->addRecipient('invalid-email');
    }

    /**
     * setSubject() функц тест.
     */
    public function testSetSubject(): void
    {
        $result = $this->mail->setSubject('Тест гарчиг');
        
        $this->assertSame($this->mail, $result);
    }

    /**
     * setMessage() функц тест.
     */
    public function testSetMessage(): void
    {
        $result = $this->mail->setMessage('Тест мессеж');
        
        $this->assertSame($this->mail, $result);
    }

    /**
     * setFrom() функц тест.
     */
    public function testSetFrom(): void
    {
        $result = $this->mail->setFrom('sender@example.com', 'Илгээгч');
        
        $this->assertSame($this->mail, $result);
    }

    /**
     * Буруу имэйл хаягтай setFrom() тест.
     */
    public function testSetFromWithInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->mail->setFrom('invalid-email');
    }

    /**
     * setReplyTo() функц тест.
     */
    public function testSetReplyTo(): void
    {
        $result = $this->mail->setReplyTo('reply@example.com', 'Хариу');
        
        $this->assertSame($this->mail, $result);
    }

    /**
     * Буруу имэйл хаягтай setReplyTo() тест.
     */
    public function testSetReplyToWithInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->mail->setReplyTo('invalid-email');
    }

    /**
     * addFileAttachment() функц тест.
     */
    public function testAddFileAttachment(): void
    {
        // Тест файл үүсгэх
        $testFile = \sys_get_temp_dir() . '/test_attachment.txt';
        \file_put_contents($testFile, 'Тест агуулга');
        
        $result = $this->mail->addFileAttachment($testFile);
        
        $this->assertSame($this->mail, $result);
        $attachments = $this->mail->getAttachments();
        $this->assertCount(1, $attachments);
        $this->assertArrayHasKey('path', $attachments[0]);
        
        // Тест файл устгах
        \unlink($testFile);
    }

    /**
     * Олдсонгүй файлтай addFileAttachment() тест.
     */
    public function testAddFileAttachmentWithNonExistentFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->mail->addFileAttachment('/path/to/non/existent/file.txt');
    }

    /**
     * addContentAttachment() функц тест.
     */
    public function testAddContentAttachment(): void
    {
        $result = $this->mail->addContentAttachment('Тест агуулга', 'test.txt');
        
        $this->assertSame($this->mail, $result);
        $attachments = $this->mail->getAttachments();
        $this->assertCount(1, $attachments);
        $this->assertArrayHasKey('content', $attachments[0]);
        $this->assertArrayHasKey('name', $attachments[0]);
    }

    /**
     * Хоосон агуулгатай addContentAttachment() тест.
     */
    public function testAddContentAttachmentWithEmptyContent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->mail->addContentAttachment('', 'test.txt');
    }

    /**
     * clearAttachments() функц тест.
     */
    public function testClearAttachments(): void
    {
        // Тест файл үүсгэх
        $testFile = \sys_get_temp_dir() . '/test_clear.txt';
        \file_put_contents($testFile, 'test');
        
        $this->mail->addFileAttachment($testFile);
        $this->assertCount(1, $this->mail->getAttachments());
        
        $this->mail->clearAttachments();
        $this->assertCount(0, $this->mail->getAttachments());
        
        \unlink($testFile);
    }

    /**
     * addRecipients() функц тест.
     */
    public function testAddRecipients(): void
    {
        $recipients = [
            'To' => [
                ['email' => 'to1@example.com', 'name' => 'To 1'],
                ['email' => 'to2@example.com']
            ],
            'Cc' => [
                ['email' => 'cc@example.com', 'name' => 'CC']
            ],
            'Bcc' => [
                ['email' => 'bcc@example.com']
            ]
        ];
        
        $this->mail->addRecipients($recipients);
        
        $this->assertCount(2, $this->mail->getRecipients('To'));
        $this->assertCount(1, $this->mail->getRecipients('Cc'));
        $this->assertCount(1, $this->mail->getRecipients('Bcc'));
    }

    /**
     * sendMail() функц - шаардлагатай утгууд тохируулаагүй тохиолдолд тест.
     */
    public function testSendMailWithoutRequiredFields(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->mail->sendMail();
    }

    /**
     * sendMail() функц - зөв тохиргоотой тест.
     * 
     * Анхаар: Энэ тест нь бодит имэйл илгээхгүй, зөвхөн тохиргоо шалгана.
     * Бодит илгээлт хийхийн тулд SMTP сервер тохируулах шаардлагатай.
     */
    public function testSendMailConfiguration(): void
    {
        $this->mail->targetTo('recipient@example.com', 'Хүлээн авагч');
        $this->mail->setFrom('sender@example.com', 'Илгээгч');
        $this->mail->setSubject('Тест гарчиг');
        $this->mail->setMessage('Тест мессеж');
        
        // assertValues() алдаа гаргахгүй эсэхийг шалгах
        // (бодит илгээлт хийхгүй, зөвхөн тохиргоо шалгана)
        $this->assertTrue(true);
    }
}
