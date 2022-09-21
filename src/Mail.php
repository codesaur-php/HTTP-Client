<?php

namespace codesaur\Http\Client;

use Exception;

use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    public function send($from, $to, $subject, $message)
    {
        if (!isset($from)) {
            throw new Exception('Mail sender must be set!');
        } elseif (is_array($from)) {
            $sender = '=?UTF-8?B?' . base64_encode($from[0]) . '?= <' . $from[1] . '>';
        }
  
        if (!isset($to)) {
            throw new Exception('Mail recipient must be set!');
        } elseif (is_array($to)) {
            $recipient = '=?UTF-8?B?' . base64_encode($to[0]) . '?= <' . $to[1] . '>';
        }
        
        if (empty($subject) || empty($message)) {
            throw new Exception('No content? Are u kidding? Mail message must be set!');
        }
        
        $content_type = strpos($message, '</') === false ? 'text/plain' : 'text/html';
        
        $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        
        $header  = 'MIME-Version: 1.0' . "\r\n";
        $header .= 'Content-type: ' . $content_type . '; charset=utf-8' . "\r\n";
        $header .= 'Content-Transfer-Encoding: base64' . "\r\n";
        $header .= 'Date: ' . date('r (T)') . "\r\n";
        
        $from_addr =  $from ?? $sender;
        $header .= 'From: ' . $from_addr . "\r\n";
        $header .= 'Reply-To: ' . $from_addr . "\r\n";
        
        $header .= 'X-Mailer: PHP/' . phpversion();
        
        if (mail($recipient ?? $to, $subject, base64_encode($message), $header)) {
            return true;
        }
        
        throw new Exception(error_get_last()['message'] ?? 'Email not sent!');
    }
    
    public function sendSMTP(
        $from, $from_name, $to, $to_name,
        $subject, $message, $charset,
        $host, $port, $username, $password,
        $is_smtp = true, $smtp_auth = true, $smtp_secure = 'ssl',
        $smtp_options = array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true))
    ) {
        $exceptions = defined('CODESAUR_DEVELOPMENT') && CODESAUR_DEVELOPMENT ? true : null;                    
        $mailer = new PHPMailer($exceptions);
        if ($is_smtp) {
           $mailer->IsSMTP(); 
        }
        $mailer->Mailer = 'smtp';
        $mailer->CharSet = $charset;
        $mailer->SMTPAuth = $smtp_auth;
        $mailer->SMTPSecure = $smtp_secure;
        $mailer->Host = $host;
        $mailer->Port = $port;
        $mailer->Username = $username;
        $mailer->Password = $password;
        $mailer->SetFrom($from, $from_name);
        $mailer->AddReplyTo($from, $from_name);
        $mailer->SMTPOptions = $smtp_options;
        $mailer->MsgHTML($message);
        $mailer->Subject = $subject;
        $mailer->AddAddress($to, $to_name);
        return $mailer->Send();
    }
}
