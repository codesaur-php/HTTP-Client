<?php

namespace codesaur\Http\Client;

/**
 * Class CurlClient
 *
 * HTTP хүсэлт илгээх зориулалттай хөнгөн жинтэй cURL клиент.
 *
 * Энэ класс нь хүссэн URL рүү дурын HTTP методоор (`GET`, `POST`,
 * `PUT`, `DELETE`, `PATCH` гэх мэт) хүсэлт илгээж, серверийн хариуг
 * текст эсвэл Response обьект хэлбэрээр буцаана.
 *
 * @package codesaur\Http\Client
 */
class CurlClient
{
    /**
     * @var bool Debug горим идэвхтэй эсэх.
     */
    private bool $debug = false;

    /**
     * @var array Debug лог бичлэгүүд.
     */
    private array $debugLog = [];

    /**
     * Debug горимыг идэвхжүүлэх/унтраах.
     *
     * Debug горим идэвхтэй үед бүх хүсэлт/хариуны дэлгэрэнгүй
     * мэдээллийг лог болгон хадгална.
     *
     * @param bool $enable True бол идэвхжүүлнэ, false бол унтраана.
     *
     * @return static Fluent interface.
     */
    public function enableDebug(bool $enable = true): static
    {
        $this->debug = $enable;
        return $this;
    }

    /**
     * Debug лог бичлэгүүдийг авах.
     *
     * @return array Бүх debug лог бичлэгүүдийн массив.
     */
    public function getDebugLog(): array
    {
        return $this->debugLog;
    }

    /**
     * Debug лог бичлэгүүдийг цэвэрлэх.
     *
     * @return static Fluent interface.
     */
    public function clearDebugLog(): static
    {
        $this->debugLog = [];
        return $this;
    }

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
     *      Хэрэглэх HTTP метод (анхдагч - `GET`).
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
        return $this->send($uri, $method, $data, $options)->body;
    }

    /**
     * HTTP хүсэлт илгээж, Response обьект буцаах.
     *
     * `request()` функцтэй адилхан ажилладаг боловч серверийн
     * хариуг Response обьект хэлбэрээр буцаана. Ингэснээр
     * status code, headers, body-г тус тусад нь авах боломжтой.
     *
     * @param string $uri
     *      Хүсэлт илгээх URL эсвэл endpoint.
     *
     * @param string $method
     *      Хэрэглэх HTTP метод (анхдагч - `GET`).
     *
     * @param string|array $data
     *      Хүсэлттэй хамт илгээх өгөгдөл.
     *      - string: текст хэлбэрээр (JSON, form-data гэх мэт)
     *      - array: multipart/form-data хэлбэрээр (файл upload-д ашиглана)
     *
     * @param array<int|string, mixed> $options
     *      cURL-ийн нэмэлт тохиргоонууд.
     *
     * @return Response
     *      Серверийн хариу (status code, headers, body бүхий обьект).
     *
     * @throws \Exception
     *      cURL гүйцэтгэх явцад алдаа гарвал.
     */
    public function send(string $uri, string $method = 'GET', string|array $data = '', array $options = []): Response
    {
        $ch = \curl_init();

        $responseHeaders = [];
        \curl_setopt_array($ch, [
            \CURLOPT_URL => $uri,
            \CURLOPT_RETURNTRANSFER => 1,
            \CURLOPT_CUSTOMREQUEST => $method,
            \CURLOPT_USERAGENT => \get_class($this) . ' cURL Request',
            \CURLOPT_HEADERFUNCTION => function ($ch, $header) use (&$responseHeaders) {
                $parts = \explode(':', $header, 2);
                if (\count($parts) === 2) {
                    $responseHeaders[\trim($parts[0])] = \trim($parts[1]);
                }
                return \strlen($header);
            }
        ]);

        if (!empty($data)) {
            \curl_setopt($ch, \CURLOPT_POSTFIELDS, $data);
            if (\is_string($data)) {
                if (!isset($options[\CURLOPT_HTTPHEADER])
                    || !\is_array($options[\CURLOPT_HTTPHEADER])
                ) {
                    $options[\CURLOPT_HTTPHEADER] = [];
                }
                $options[\CURLOPT_HTTPHEADER][] = 'Content-Length: ' . \strlen($data);
            }
        }
        foreach ($options as $option => $value) {
            \curl_setopt($ch, $option, $value);
        }
        $body = \curl_exec($ch);
        if ($body === false) {
            $code = \curl_errno($ch);
            $message = \curl_error($ch);

            \curl_close($ch);

            throw new \Exception($message, $code);
        }

        $statusCode = (int)\curl_getinfo($ch, \CURLINFO_HTTP_CODE);

        if ($this->debug) {
            $this->debugLog[] = [
                'uri' => $uri,
                'method' => $method,
                'status' => $statusCode,
                'request_data' => \is_string($data) ? $data : '(multipart)',
                'response_headers' => $responseHeaders,
                'response_body' => $body,
                'curl_info' => \curl_getinfo($ch),
            ];
        }

        \curl_close($ch);

        return new Response($statusCode, $responseHeaders, $body);
    }

    /**
     * HTTP хүсэлт илгээх, амжилтгүй бол дахин оролдох.
     *
     * Серверийн алдаа (5xx) эсвэл сүлжээний алдаа гарсан үед
     * exponential backoff ашиглан автоматаар дахин оролдоно.
     * Клиентийн алдаа (4xx) гарвал дахин оролдохгүй.
     *
     * @param string $uri
     *      Хүсэлт илгээх URL.
     *
     * @param string $method
     *      HTTP метод (анхдагч - `GET`).
     *
     * @param string|array $data
     *      Илгээх өгөгдөл.
     *
     * @param array $options
     *      cURL тохиргоонууд.
     *
     * @param int $retries
     *      Дахин оролдох тоо (анхдагч - 3).
     *
     * @param int $delayMs
     *      Эхний хүлээх хугацаа миллисекундээр (анхдагч - 500).
     *      Дараагийн оролдлого бүрт 2 дахин нэмэгдэнэ (exponential backoff).
     *
     * @return Response
     *      Серверийн хариу.
     *
     * @throws \Exception
     *      Бүх оролдлого амжилтгүй болсон үед.
     */
    public function sendWithRetry(
        string $uri,
        string $method = 'GET',
        string|array $data = '',
        array $options = [],
        int $retries = 3,
        int $delayMs = 500
    ): Response {
        $lastException = null;

        for ($attempt = 0; $attempt <= $retries; $attempt++) {
            try {
                $response = $this->send($uri, $method, $data, $options);

                // Амжилттай эсвэл клиент алдаа (4xx) бол дахин оролдохгүй
                if ($response->statusCode < 500 || $attempt === $retries) {
                    return $response;
                }
            } catch (\Exception $e) {
                $lastException = $e;
                if ($attempt === $retries) {
                    throw $lastException;
                }
            }

            // Exponential backoff: 500ms, 1000ms, 2000ms, ...
            \usleep($delayMs * 1000 * (int)\pow(2, $attempt));
        }

        throw $lastException ?? new \Exception('All retry attempts failed');
    }

    /**
     * Файл upload хийх.
     *
     * CURLFile ашиглан multipart/form-data хэлбэрээр файл илгээнэ.
     *
     * @param string $uri
     *      Upload хийх URL.
     *
     * @param string $filePath
     *      Илгээх файлын зам.
     *
     * @param string $fieldName
     *      Серверт илгээх form field нэр (анхдагч - 'file').
     *
     * @param array $fields
     *      Файлын хамт илгээх нэмэлт form field-үүд.
     *
     * @param array $options
     *      Нэмэлт cURL тохиргоонууд.
     *
     * @return Response
     *      Серверийн хариу.
     *
     * @throws \Exception
     *      Файл олдохгүй эсвэл сүлжээний алдаа гарвал.
     */
    public function upload(
        string $uri,
        string $filePath,
        string $fieldName = 'file',
        array $fields = [],
        array $options = []
    ): Response {
        if (!\file_exists($filePath)) {
            throw new \Exception("File not found: $filePath");
        }

        $fields[$fieldName] = new \CURLFile($filePath);

        return $this->send($uri, 'POST', $fields, $options);
    }
}
