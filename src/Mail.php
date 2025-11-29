<?php

namespace codesaur\Http\Client;

/**
 * Class Mail
 *
 * PHP-ийн `mail()` функцийг ашиглан MIME стандарттай,
 * хавсралт бүхий эсвэл энгийн имэйл илгээхэд зориулагдсан
 * хөнгөн жинтэй имэйл клиент класс.
 *
 * Онцлогууд:
 *  - To / Cc / Bcc хүлээн авагчид удирдах
 *  - Элгэн (inline) болон олон төрлийн хавсралт дэмжих:
 *        - file path → addFileAttachment()
 *        - URL → addUrlAttachment()
 *        - raw content → addContentAttachment()
 *  - HTML болон plaintext имэйл илгээх
 *  - UTF-8 encoded header & filename бүрэн дэмжлэг
 *  - MIME multipart имэйл автоматаар үүсгэнэ
 *
 * @package codesaur\Http\Client
 */
class Mail
{
    /**
     * @var array Хүлээн авагчдын жагсаалт (To, Cc, Bcc)
     */
    private array $_recipients;

    /**
     * @var string Илгээгчийн имэйл хаяг
     */
    protected string $from;

    /**
     * @var string Илгээгчийн нэр
     */
    protected string $fromName = '';

    /**
     * @var string Хариу илгээх имэйл
     */
    protected string $replyTo = '';

    /**
     * @var string Хариу илгээх нэр
     */
    protected string $replyToName = '';

    /**
     * @var string Имэйл гарчиг
     */
    protected string $subject;

    /**
     * @var string Имэйл мессежийн гол агуулга
     */
    protected string $message;

    /**
     * @var array Хавсралтын жагсаалт
     */
    private array $_attachments;

    /**
     * Олон хүлээн авагчийг reset хийж, зөвхөн нэг шинэ recipient оноох.
     *
     * @param string $email
     * @param string $name
     * @return $this
     */
    public function targetTo(string $email, string $name = '')
    {
        $this->_recipients = [];
        return $this->addRecipient($email, $name);
    }

    /**
     * To хүлээн авагч нэмэх.
     *
     * @return $this
     */
    public function addRecipient(string $email, string $name = '')
    {
        $this->appendRecipient('To', $email, $name);
        return $this;
    }

    /**
     * Cc хүлээн авагч нэмэх.
     *
     * @return $this
     */
    public function addCCRecipient(string $email, string $name = '')
    {
        $this->appendRecipient('Cc', $email, $name);
        return $this;
    }

    /**
     * Bcc хүлээн авагч нэмэх.
     *
     * @return $this
     */
    public function addBCCRecipient(string $email, string $name = '')
    {
        $this->appendRecipient('Bcc', $email, $name);
        return $this;
    }

    /**
     * Дотоод функц — хүлээн авагчдын массив руу төрөлтэй нь нэмэх.
     *
     * @throws \InvalidArgumentException
     */
    private function appendRecipient(string $type, string $email, string $name = '')
    {
        if (!\filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address for a recipient [$email]!");
        }

        if (!isset($this->_recipients) || !\is_array($this->_recipients)) {
            $this->_recipients = [];
        }
        if (!isset($this->_recipients[$type]) || !\is_array($this->_recipients[$type])) {
            $this->_recipients[$type] = [];
        }

        $entry = ['email' => $email];
        if (!empty($name)) {
            $entry['name'] = $name;
        }

        $this->_recipients[$type][] = $entry;
    }

    /**
     * Олон хүлээн авагчийг массив хэлбэрээр нэмэх.
     *
     * @param array $recipients ['To'=>[], 'Cc'=>[], 'Bcc'=>[]]
     * @return $this
     */
    public function addRecipients(array $recipients)
    {
        foreach ($recipients as $type => $recipient) {
            if (!\is_array($recipient)) continue;

            foreach ($recipient as $person) {
                try {
                    switch ($type) {
                        case 'To':
                            $this->addRecipient($person['email'] ?? 'null', $person['name'] ?? '');
                            break;
                        case 'Cc':
                            $this->addCCRecipient($person['email'] ?? 'null', $person['name'] ?? '');
                            break;
                        case 'Bcc':
                            $this->addBCCRecipient($person['email'] ?? 'null', $person['name'] ?? '');
                            break;
                    }
                } catch (\Throwable $e) {
                    if (CODESAUR_DEVELOPMENT) {
                        \error_log($e->getMessage());
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Тодорхой төрлийн хүлээн авагчдын жагсаалтыг буцаах.
     *
     * @param string $type To|Cc|Bcc
     */
    public function getRecipients(string $type): array
    {
        if (!isset($this->_recipients[$type])
            || !\is_array($this->_recipients[$type])
        ) {
            return [];
        }
        return $this->_recipients[$type];
    }

    /**
     * Имэйлийн гарчиг тохируулах.
     *
     * @return $this
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Имэйлийн гол агуулга тохируулах.
     *
     * @return $this
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Илгээгчийн мэдээлэл тохируулах.
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setFrom(string $email, string $name = '')
    {
        if (!\filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address for a <from> field [$email]!");
        }

        $this->from = $email;
        $this->fromName = $name;
        return $this;
    }

    /**
     * Reply-To тохируулах.
     *
     * @return $this
     */
    public function setReplyTo(string $email, string $name = '')
    {
        if (!\filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email for <reply-to> [$email]!");
        }

        $this->replyTo = $email;
        $this->replyToName = $name;
        return $this;
    }

    /**
     * Хавсралт нэмж өгөгдсөн төрөлд үндэслэн чиглүүлэх.
     *
     * @throws \InvalidArgumentException
     */
    public function addAttachment(array $attachment)
    {
        if (isset($attachment['path']) && !empty($attachment['path'])) {
            $this->addFileAttachment($attachment['path']);
        } elseif (isset($attachment['url'])) {
            $this->addUrlAttachment($attachment['url']);
        } elseif (isset($attachment['content']) && isset($attachment['name'])) {
            $this->addContentAttachment($attachment['content'], $attachment['name']);
        } else {
            throw new \InvalidArgumentException('Invalid attachment!');
        }

        return $this;
    }

    /**
     * Файлын замаар хавсралт нэмэх.
     *
     * @throws \InvalidArgumentException
     */
    public function addFileAttachment(string $filePath)
    {
        if (!\is_file($filePath)) {
            throw new \InvalidArgumentException('Invalid file attachment!');
        }

        $this->appendAttachment([
            'path' => $filePath,
            'name' => \basename($filePath)
        ]);

        return $this;
    }

    /**
     * URL дээрх файлыг хавсралтад нэмэх.
     *
     * @throws \InvalidArgumentException
     */
    public function addUrlAttachment(string $fileUrl)
    {
        $headers = \get_headers($fileUrl);
        if (\stripos($headers[0], '200 OK') === false) {
            throw new \InvalidArgumentException('Invalid URL attachment!');
        }

        $path = \parse_url($fileUrl, \PHP_URL_PATH);
        $fileName = \basename($path);

        $this->appendAttachment(['url' => $fileUrl, 'name' => $fileName]);
        return $this;
    }

    /**
     * Raw binary өгөгдлөөр хавсралт нэмэх.
     */
    public function addContentAttachment(string $fileContent, string $fileName)
    {
        if (empty($fileContent) || empty($fileName)) {
            throw new \InvalidArgumentException('Empty attachment content!');
        }

        $this->appendAttachment(['content' => $fileContent, 'name' => $fileName]);
        return $this;
    }

    /**
     * Дотоод хавсралт массив руу нэгж хавсралт нэмэх.
     */
    private function appendAttachment(array $attachment)
    {
        if (!isset($this->_attachments)
            || !\is_array($this->_attachments)
        ) {
            $this->clearAttachments();
        }

        $this->_attachments[] = $attachment;
    }

    /**
     * Хавсралтуудыг хоослох.
     */
    public function clearAttachments()
    {
        $this->_attachments = [];
    }

    /**
     * Хавсралтын жагсаалт буцаах.
     */
    public function getAttachments(): array
    {
        return $this->_attachments ?? [];
    }

    /**
     * Дотоод — Имэйл хаягийг MIME стандартын дагуу форматлах.
     */
    private function getAddressLine(array $address): string
    {
        if (!isset($address['email'])
            || empty($address['email'])
        ) {
            throw new \InvalidArgumentException('Email address is not set!');
        }

        if (empty($address['name'])) {
            return $address['email'];
        }

        return $this->getEncodedStr($address['name']) . " <{$address['email']}>";
    }

    /**
     * MIME header-д зориулсан UTF-8 Base64 encode.
     */
    private function getEncodedStr(string $value): string
    {
        return !\preg_match('/[^A-Za-z0-9._+\-\(\)\[\]]+/', $value)
            ? $value
            : '=?utf-8?B?' . \base64_encode($value) . '?=';
    }

    /**
     * Имэйл илгээхээс өмнө шаардлагатай бүх утгууд байгаа эсэхийг шалгах.
     *
     * @throws \InvalidArgumentException
     */
    protected function assertValues()
    {
        $recipients = $this->getRecipients('To');

        if (!isset($recipients[0]['email'])) {
            throw new \InvalidArgumentException('Mail recipient must be set!');
        } elseif (empty($this->from)) {
            throw new \InvalidArgumentException('Mail sender <from> must be set!');
        } elseif (empty($this->subject) || empty($this->message)) {
            throw new \InvalidArgumentException('Mail (subject & message) must be set!');
        }
    }

    /**
     * Имэйл илгээх үндсэн функц.
     *
     * MIME multipart имэйл үүсгэж, хавсралттай/хавсралтгүй илгээнэ.
     *
     * @return bool Имэйл амжилттай илгээгдсэн эсэх
     * @throws \RuntimeException Илгээх явцад алдаа гарвал
     */
    public function sendMail(): bool
    {
        $this->assertValues();
        \mb_internal_encoding('UTF-8');
        
        if (\str_contains($this->message, '</')) {
            $content_type = 'text/html';
            $content_encoding = "Content-Transfer-Encoding: base64\r\n";
            $chunked_message = \chunk_split(\base64_encode($this->message));
        } else {
            $content_type = 'text/plain';
            $content_encoding = '';
            $chunked_message = "$this->message\r\n";
        }
        $encoded_subject = \mb_encode_mimeheader("Subject: $this->subject", 'UTF-8');
        $subject = \substr($encoded_subject, \strlen('Subject: '));
        $attachments = $this->getAttachments();
        $is_attached = !empty($attachments);
        $semi_rand = \md5(\time());
        $mime_boundary = "Multipart_Boundary_x{$semi_rand}x";
        $toAddresses = [];
        foreach ($this->getRecipients('To') as $to) {
            $toAddresses[] = $this->getAddressLine($to);
        }
        $ccAddresses = [];
        foreach ($this->getRecipients('Cc') as $cc) {
            $ccAddresses[] = $this->getAddressLine($cc);
        }
        $bccAddresses = [];
        foreach ($this->getRecipients('Bcc') as $bcc) {
            $bccAddresses[] = $this->getAddressLine($bcc);
        }
        $from = $this->getAddressLine(['email' => $this->from, 'name' => $this->fromName]);
        if (isset($this->replyTo) && !empty($this->replyTo)) {
            $replyTo = $this->getAddressLine(['email' => $this->replyTo, 'name' => $this->replyToName]);
        } else {
            $replyTo = $from;
        }
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= 'Date: ' . \date('r (T)') . "\r\n";
        $headers .= "From: $from\r\n";
        if (!empty($ccAddresses)) {
            $headers .= 'Cc: ' . \implode(",\n\t", $ccAddresses). "\r\n";
        }
        if (!empty($bccAddresses)) {
            $headers .= 'Bcc: ' . \implode(",\n\t", $bccAddresses). "\r\n";
        }
        $headers .= "Reply-To: $replyTo\r\n";
        $headers .= 'X-Mailer: PHP/' . \phpversion() . "\r\n";
        
        if (!$is_attached) {
            $headers .= $content_encoding;
            $headers .= "Content-Type: $content_type; charset=\"utf-8\"";
            $message = $chunked_message;
        } else {
            $headers .= "Content-Type: multipart/mixed;\n\tboundary=\"$mime_boundary\"";
            $message = "--$mime_boundary\r\n";
            $message .= $content_encoding;
            $message .= "Content-Type: $content_type; charset=\"utf-8\"\r\n\r\n";
            $message .= "$chunked_message\r\n";
            foreach ($attachments as $attachment) {
                $message .= "--$mime_boundary\r\n";
                $name = $this->getEncodedStr($attachment['name']);
                if (isset($attachment['path'])) {
                    $type = \mime_content_type($attachment['path']);
                    $size = \filesize($attachment['path']);
                    $fp = \fopen($attachment['path'], 'rb');
                    $data = \fread($fp, $size);
                    \fclose($fp);
                } elseif (isset($attachment['url'])) {
                    $data = \file_get_contents($attachment['url']);
                    $finfo = new \finfo(\FILEINFO_MIME_TYPE);
                    $type = $finfo->buffer($data);
                } elseif (isset($attachment['content'])) {
                    $data = $attachment['content'];
                    $finfo = new \finfo(\FILEINFO_MIME_TYPE);
                    $type = $finfo->buffer($data);
                } else {
                    continue;
                }
                $data = \chunk_split(\base64_encode($data));
                $type = \strtoupper($type);
                $message .= "Content-Type: $type; name=\"$name\"\r\n";
                $message .= "Content-Transfer-Encoding: base64\r\n";
                $message .= "Content-Disposition: attachment; filename=\"$name\"\r\n";
                $message .= "\r\n$data\r\n";
            }
            $message .= "--$mime_boundary--";
        }
        
        $recipient = \implode(",", $toAddresses);
        if (\mail($recipient, $subject, $message, $headers)) {
            return true;
        }

        throw new \RuntimeException(\error_get_last()['message'] ?? 'Email not sent!');
    }
}
