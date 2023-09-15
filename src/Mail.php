<?php

namespace codesaur\Http\Client;

class Mail
{
    protected array $recipients;
    
    protected string $from;
    protected string $fromName = '';
    
    protected string $replyTo = '';
    protected string $replyToName = '';
    
    protected string $subject;
    protected string $message;
    protected array $attachments;
    
    protected string $languageCode = '';
    
    public function targetTo(string $email, string $name = '')
    {
        $this->recipients = [];

        $this->addRecipient($email, $name);
    }

    public function addRecipient(string $email, string $name = '')
    {
        $this->appendRecipient('To', $email, $name);
    }

    public function addCCRecipient(string $email, string $name = '')
    {
        $this->appendRecipient('Cc', $email, $name);
    }

    public function addBCCRecipient(string $email, string $name = '')
    {
        $this->appendRecipient('Bcc', $email, $name);
    }

    private function appendRecipient(string $type, string $email, string $name = '')
    {
        if (!\filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address for a recipient [$email]! ");
        }

        if (!isset($this->recipients)
            || !\is_array($this->recipients)
        ) {
            $this->recipients = [];
        }
        if (!isset($this->recipients[$type])
            || !\is_array($this->recipients[$type])
        ) {
            $this->recipients[$type] = [];
        }

        $this->recipients[$type][] = ['name' => \str_replace(',', '', $name), 'email' => $email];
    }

    public function getRecipients(string $type): array
    {
        if (!isset($this->recipients[$type])
            || !\is_array($this->recipients[$type])
        ) {
            return [];
        }

        return $this->recipients[$type];
    }

    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    public function setFrom(string $email, string $name = '')
    {
        if (!\filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address for a <from> field [$email]! ");
        }

        $this->from = $email;
        $this->fromName = \str_replace(',', '', $name);
    }

    public function setReplyTo(string $email, string $name = '')
    {
        if (!\filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address for a <reply-to> field [$email]! ");
        }

        $this->replyTo = $email;
        $this->replyToName = \str_replace(',', '', $name);
    }

    public function setLanguageCode(string $languageCode)
    {
        $this->languageCode = $languageCode;
    }

    public function addAttachment(array $attachment)
    {
        if (isset($attachment['file_path'])
            && !empty($attachment['file_path'])
        ) {
            $this->addFileAttachment($attachment['file_path'], $attachment['file_name'] ?? '');
        } elseif (
            isset($attachment['file_content'])
            && !empty($attachment['file_content'])
            && isset($attachment['file_name'])
            && !empty($attachment['file_name'])
        ) {
            $this->addContentAttachment($attachment['file_content'], $attachment['file_name']);
        } else {
            throw new \InvalidArgumentException('Invalid attachment!');
        }
    }

    public function addFileAttachment(string $filePath, string $fileName = '')
    {
        $this->appendAttachment(['file_path' => $filePath, 'file_name' => $fileName]);
    }

    public function addContentAttachment(string $fileContent, string $fileName)
    {
        $this->appendAttachment(['file_content' => $fileContent, 'file_name' => $fileName]);
    }

    private function appendAttachment(array $attachment)
    {
        if (!isset($this->attachments) || !\is_array($this->attachments)
        ) {
            $this->attachments = [];
        }

        $this->attachments[] = $attachment;
    }

    public function clearAttachments()
    {
        $this->attachments = [];
    }

    private function getAddressLine(array $address): string
    {
        if (!isset($address['email']) || empty($address['email'])) {
            throw new \InvalidArgumentException('Email address is not set!');
        }

        if (!isset($address['name']) || empty($address['name'])) {
            return $address['email'];
        } else {
            return \base64_encode($address['name']) . " <{$address['email']}>";
        }
    }

    protected function assertValues()
    {
        $recipients = $this->getRecipients('To');
        if (!isset($recipients[0]) || !\is_array($recipients[0]) || empty($recipients[0]['email'])
        ) {
            throw new \InvalidArgumentException('Mail recipient must be set!');
        } elseif (empty($this->from)) {
            throw new \InvalidArgumentException('Mail sender <from> must be set!');
        } elseif (empty($this->subject) || empty($this->message)
        ) {
            throw new \InvalidArgumentException('No content? Are u kidding? Mail (subject : message) must be set!');
        }
    }

    public function send(): bool
    {
        $this->assertValues();

        $content_type = \str_contains($this->message, '</') ? 'text/html' : 'text/plain';
        $subject = '=?UTF-8?B?' . \base64_encode($this->subject) . '?=';

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

        $headers = 'MIME-Version: 1.0' . "\r\n";
        
        $is_attachment = !empty($this->attachments) && \is_array($this->attachments);
        if ($is_attachment) {
            $semi_rand = \md5(\time());
            $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
            $headers .= 'Content-Type: multipart/mixed;\n' . " boundary=\"{$mime_boundary}\"";
            $message = "--{$mime_boundary}\n" . 'Content-Type: ' . $content_type . '; charset=UTF-8' . "\r\n" .
                "Content-Transfer-Encoding: base64\n\n" . $this->message . "\n\n";
            foreach ($this->attachments as $attachment) {
                if (isset($attachment['file_path']) && !empty($attachment['file_path'])) {
                    if (!\is_file($attachment['file_path'])) {
                        continue;
                    }
                    $file_name =
                        isset($attachment['file_name']) && !empty($attachment['file_name'])
                        ? $attachment['file_name'] : \basename($attachment['file_path']);
                    $file_size = \filesize($attachment['file_path']);
                    $fp = @\fopen($attachment['file_path'], 'rb');
                    $data = @\fread($fp, $file_size);
                    @\fclose($fp);
                } elseif (
                    isset($attachment['file_content'])
                    && !empty($attachment['file_content'])
                    && isset($attachment['file_name'])
                    && !empty($attachment['file_name'])
                ) {
                    $file_name = $attachment['file_name'];
                    $data = $attachment['file_content'];
                    $file_size = \strlen($attachment['file_content']);
                } else {
                    continue;
                }
                $message .= "--{$mime_boundary}\n";
                $data = \chunk_split(\base64_encode($data));
                $message .= "Content-Type: application/octet-stream; name=\"" . $file_name . "\"\n" .
                    "Content-Description: " . $file_name . "\n" .
                    "Content-Disposition: attachment;\n" . " filename=\"" . $file_name . "\"; size=" . $file_size . ";\n" .
                    "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
            }
            $message .= "--{$mime_boundary}--";
        } else {
            $headers .= 'Content-Type: ' . $content_type . '; charset=UTF-8' . "\r\n";
            $headers .= 'Content-Transfer-Encoding: base64' . "\r\n";
            $message = \base64_encode($this->message);
        }
        
        $headers .= 'Date: ' . \date('r (T)') . "\r\n";
        $headers .= 'From: ' . $from . "\r\n";
        if (!empty($ccAddresses)) {
            $headers .= 'Cc: ' . \implode(',', $ccAddresses) . "\r\n";
        }
        if (!empty($bccAddresses)) {
            $headers .= 'Bcc: ' . \implode(',', $bccAddresses) . "\r\n";
        }
        $headers .= 'Reply-To: ' . $replyTo . "\r\n";
        $headers .= 'X-Mailer: PHP/' . \phpversion();

        $recipient = \implode(',', $toAddresses);
        if (\mail($recipient, $subject, $message, $headers)) {
            return true;
        }

        throw new \RuntimeException(\error_get_last()['message'] ?? 'Email not sent!');
    }

    public function sendSMTP(
        string $host, int $port,
        string $username, string $password,
        string $smtp_secure = 'ssl', ?array $smtp_options = null
    ): bool
    {
        $this->assertValues();

        $exceptions = \defined('CODESAUR_DEVELOPMENT') && CODESAUR_DEVELOPMENT ? true : null;

        if (empty($smtp_options)) {
            $smtp_options = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
        }

        $mailer = new \PHPMailer\PHPMailer\PHPMailer($exceptions);
        $mailer->isSMTP();
        $mailer->CharSet = 'UTF-8';
        $mailer->SMTPAuth = true;
        $mailer->SMTPSecure = $smtp_secure;
        $mailer->Host = $host;
        $mailer->Port = $port;
        $mailer->Username = $username;
        $mailer->Password = $password;
        $mailer->setFrom($this->from, $this->fromName);
        if (empty($this->replyTo)) {
            $replyTo = $this->from;
            $replyToName = $this->fromName;
        } else {
            $replyTo = $this->replyTo;
            $replyToName = $this->replyToName;
        }
        $mailer->addReplyTo($replyTo, $replyToName);
        $mailer->SMTPOptions = $smtp_options;
        $mailer->msgHTML($this->message);
        $mailer->Subject = $this->subject;
        foreach ($this->getRecipients('To') as $to) {
            $mailer->addAddress($to['email'], $to['name'] ?? '');
        }
        foreach ($this->getRecipients('Cc') as $cc) {
            $mailer->addCC($cc['email'], $cc['name'] ?? '');
        }
        foreach ($this->getRecipients('Bcc') as $bcc) {
            $mailer->addBCC($bcc['email'], $bcc['name'] ?? '');
        }

        if (!empty($this->attachments) && \is_array($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                if (isset($attachment['file_path'])
                    && !empty($attachment['file_path'])
                ) {
                    $mailer->addAttachment($attachment['file_path'], $attachment['file_name'] ?? '');
                } elseif (
                    isset($attachment['file_content'])
                    && !empty($attachment['file_content'])
                    && isset($attachment['file_name'])
                    && !empty($attachment['file_name'])
                ) {
                    $mailer->addStringAttachment($attachment['file_content'], $attachment['file_name']);
                }
            }
        }

        if (!empty($this->languageCode)) {
            $mailer->setLanguage($this->languageCode);
        }

        return $mailer->send();
    }
}
