<?php

namespace codesaur\Http\Client;

/**
 * Class JSONClient
 *
 * JSON суурьтай HTTP хүсэлтүүд илгээх зориулалттай клиент.
 * 
 * Энэхүү класс нь CurlClient дээр суурилан ажилладаг бөгөөд
 * JSON payload-той GET, POST, PUT, DELETE хүсэлтүүдийг
 * хялбараар илгээж, серверийн хариуг PHP массив хэлбэрээр буцаана.
 *
 * Алдаа гарсан тохиолдолд Exception шидэхийн оронд:
 *
 *      ['error' => ['code' => ..., 'message' => ...]]
 *
 * хэлбэртэй массив буцаана.
 *
 * @package codesaur\Http\Client
 */
class JSONClient
{
    /**
     * JSON GET хүсэлт илгээх.
     *
     * @param string $uri
     *      Хандах URL.
     *
     * @param array $payload
     *      Query string болгон нэмэгдэх өгөгдөл.
     *
     * @param array $headers
     *      Нэмэлт HTTP headers (`name => value` хэлбэр).
     *
     * @return array
     *      Дecode хийгдсэн JSON хариу эсвэл алдааны бүтэц.
     */
    public function get(string $uri, array $payload = [], array $headers = []): array
    {
        return $this->request($uri, 'GET', $payload, $headers);
    }

    /**
     * JSON POST хүсэлт илгээх.
     *
     * @param string $uri
     *      Хандах URL.
     *
     * @param array $payload
     *      JSON болгон илгээх өгөгдөл.
     *
     * @param array $headers
     *      Нэмэлт HTTP headers.
     *
     * @return array
     *      Серверийн JSON хариу.
     */
    public function post(string $uri, array $payload, array $headers = []): array
    {
        return $this->request($uri, 'POST', $payload, $headers);
    }

    /**
     * JSON PUT хүсэлт илгээх.
     */
    public function put(string $uri, array $payload, array $headers = []): array
    {
        return $this->request($uri, 'PUT', $payload, $headers);
    }

    /**
     * JSON DELETE хүсэлт илгээх.
     */
    public function delete(string $uri, array $payload, array $headers = []): array
    {
        return $this->request($uri, 'DELETE', $payload, $headers);
    }

    /**
     * JSON HTTP хүсэлт илгээх үндсэн функц.
     *
     * Энэ функц нь CurlClient ашиглан хүсэлт илгээж,
     * JSON хариуг PHP массив болгон decode хийн буцаана.
     * 
     * ✔ Payload-ийг автоматаар JSON болгоно  
     * ✔ Content-Type: application/json header автоматаар нэмнэ  
     * ✔ SSL verify (host, peer) хөгжүүлэлтийн үеийн тулд унтраалттай  
     * ✔ JSON decode алдааг шалгана  
     * ✔ Бүх алдааг нэг мөрөөр 'error' бүтэц болгон буцаана  
     *
     * @param string $uri
     *      Хүсэлт илгээх URL.
     *
     * @param string $method
     *      HTTP метод (GET, POST, PUT, DELETE).
     *
     * @param array $payload
     *      Илгээх өгөгдөл.
     *
     * @param array $headers
     *      Нэмэлт headers (`key => value`).
     *
     * @return array
     *      JSON decode хийгдсэн хариу массив,
     *      эсвэл:
     *         ['error' => ['code' => ..., 'message' => ...]]
     */
    public function request(string $uri, string $method, array $payload, array $headers): array
    {
        try {
            $header = ['Content-Type: application/json'];

            foreach ($headers as $index => $field) {
                $header[] = "$index: $field";
            }

            $options = [
                \CURLOPT_SSL_VERIFYHOST => false,
                \CURLOPT_SSL_VERIFYPEER => false,
                \CURLOPT_HTTPHEADER     => $header
            ];

            $data = empty($payload)
                ? (\strtoupper($method) == 'GET' ? '' : '{}')
                : (\json_encode($payload) ?: throw new \Exception(__CLASS__ . ': Error encoding request payload!'));

            return \json_decode(
                (new CurlClient())->request($uri, $method, $data, $options),
                true
            ) ?? throw new \Exception(__CLASS__ . ": [$uri] Response json cannot be decoded!");
        } catch (\Throwable $e) {
            return ['error' => ['code' => $e->getCode(), 'message' => $e->getMessage()]];
        }
    }
}
