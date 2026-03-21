<?php

namespace codesaur\Http\Client;

/**
 * Class Response
 *
 * HTTP хариуг (response) илэрхийлэх обьект.
 *
 * Серверээс ирсэн хариуны status code, headers, body-г
 * тус тусад нь хандах боломж олгоно.
 *
 * @package codesaur\Http\Client
 */
class Response
{
    /**
     * Response обьект үүсгэх.
     *
     * @param int $statusCode HTTP статус код (200, 404, 500 гэх мэт).
     * @param array<string, string> $headers Хариуны HTTP headers.
     * @param string $body Хариуны бие (response body).
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly array $headers,
        public readonly string $body
    ) {
    }

    /**
     * Хариуны body-г JSON массив болгон decode хийх.
     *
     * @return array|null Decode хийгдсэн массив, эсвэл null (JSON биш бол).
     */
    public function json(): ?array
    {
        return \json_decode($this->body, true);
    }

    /**
     * Хариу амжилттай эсэхийг шалгах (2xx статус код).
     *
     * @return bool 200-299 хооронд бол true.
     */
    public function isOk(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Хариу алдаатай эсэхийг шалгах (4xx эсвэл 5xx статус код).
     *
     * @return bool 400 ба түүнээс дээш бол true.
     */
    public function isError(): bool
    {
        return $this->statusCode >= 400;
    }

    /**
     * Тодорхой нэртэй header-ийн утгыг авах.
     *
     * @param string $name Header нэр (жишээ: 'Content-Type').
     * @param string|null $default Header олдохгүй бол буцаах анхдагч утга.
     *
     * @return string|null Header-ийн утга эсвэл анхдагч утга.
     */
    public function getHeader(string $name, ?string $default = null): ?string
    {
        // Том жижиг үсэг ялгахгүйгээр хайх
        foreach ($this->headers as $key => $value) {
            if (\strcasecmp($key, $name) === 0) {
                return $value;
            }
        }
        return $default;
    }
}
