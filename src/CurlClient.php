<?php

namespace codesaur\Http\Client;

/**
 * Class CurlClient
 *
 * HTTP хүсэлт илгээх зориулалттай хөнгөн жинтэй cURL клиент.
 * 
 * Энэ класс нь хүссэн URL рүү дурын HTTP методоор (`GET`, `POST`,
 * `PUT`, `DELETE` гэх мэт) хүсэлт илгээж, серверийн хариуг
 * текст хэлбэрээр буцаана.
 *
 * @package codesaur\Http\Client
 */
class CurlClient
{
    /**
     * Өгөгдсөн URL рүү HTTP хүсэлт илгээх үндсэн функц.
     *
     * Энэ функц нь cURL ашиглан сервер рүү хүсэлт илгээж,
     * буцаасан хариуг (response body) текст хэлбэрээр буцаана.
     * Хэрэв сүлжээний алдаа гарвал Exception үүсгэнэ.
     *
     * @param string $uri  
     *      Хүсэлт илгээх URL эсвэл endpoint.
     *
     * @param string $method  
     *      Хэрэглэх HTTP метод (анхдагч — `GET`).
     *
     * @param string $data  
     *      Хүсэлттэй хамт илгээх өгөгдөл. Хэрэв хоосон биш бол
     *      `CURLOPT_POSTFIELDS` тохиргоонд автоматаар нэмэгдэнэ.
     *
     * @param array<int|string, mixed> $options  
     *      cURL-ийн нэмэлт тохиргоонууд. Жишээ нь:
     *      - `CURLOPT_TIMEOUT => 10`
     *      - `CURLOPT_HTTPHEADER => ['Content-Type: application/json']`
     *
     * @return string  
     *      Серверийн хариу (response body) зөв гүйцэтгэлтэй үед.
     *
     * @throws \Exception  
     *      cURL гүйцэтгэх явцад алдаа гарвал `Exception` үүсгэнэ.
     *      Алдааны код нь `curl_errno()`, алдааны текст нь
     *      `curl_error()`-ийн утга байна.
     */
    public function request(string $uri, string $method = 'GET', string $data = '', array $options = []): string
    {
        $ch = \curl_init();
        \curl_setopt_array($ch, [
            \CURLOPT_URL => $uri,
            \CURLOPT_RETURNTRANSFER => 1,
            \CURLOPT_CUSTOMREQUEST => $method,
            \CURLOPT_USERAGENT => \get_class($this) . ' cURL Request'
        ]);

        if (!empty($data)) {
            \curl_setopt($ch, \CURLOPT_POSTFIELDS, $data);
            $options[\CURLOPT_HTTPHEADER][] = 'Content-Length: ' . \strlen($data);
        }
        foreach ($options as $option => $value) {
            \curl_setopt($ch, $option, $value);
        }
        $response = \curl_exec($ch);
        if ($response === false) {
            $code = \curl_errno($ch);
            $message = \curl_error($ch);
            
            \curl_close($ch);
            
            throw new \Exception($message, $code);
        }
        
        \curl_close($ch);

        return $response;
    }
}
