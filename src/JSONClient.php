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
     * @param array $options
     *      Нэмэлт cURL options (жишээ нь: CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1).
     *
     * @return array
     *      Дecode хийгдсэн JSON хариу эсвэл алдааны бүтэц.
     */
    public function get(string $uri, array $payload = [], array $headers = [], array $options = []): array
    {
        return $this->request($uri, 'GET', $payload, $headers, $options);
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
     * @param array $options
     *      Нэмэлт cURL options (жишээ нь: CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1).
     *
     * @return array
     *      Серверийн JSON хариу.
     */
    public function post(string $uri, array $payload, array $headers = [], array $options = []): array
    {
        return $this->request($uri, 'POST', $payload, $headers, $options);
    }

    /**
     * JSON PUT хүсэлт илгээх.
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
     * @param array $options
     *      Нэмэлт cURL options (жишээ нь: CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1).
     *
     * @return array
     *      Серверийн JSON хариу.
     */
    public function put(string $uri, array $payload, array $headers = [], array $options = []): array
    {
        return $this->request($uri, 'PUT', $payload, $headers, $options);
    }

    /**
     * JSON DELETE хүсэлт илгээх.
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
     * @param array $options
     *      Нэмэлт cURL options (жишээ нь: CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1).
     *
     * @return array
     *      Серверийн JSON хариу.
     */
    public function delete(string $uri, array $payload, array $headers = [], array $options = []): array
    {
        return $this->request($uri, 'DELETE', $payload, $headers, $options);
    }

    /**
     * JSON HTTP хүсэлт илгээх үндсэн функц.
     *
     * Энэ функц нь CurlClient ашиглан хүсэлт илгээж,
     * JSON хариуг PHP массив болгон decode хийн буцаана.
     *
     * ✔ Payload-ийг автоматаар JSON болгоно
     * ✔ Content-Type: application/json header автоматаар нэмнэ
     * ✔ SSL verify нь CODESAUR_APP_ENV environment variable-аас хамаарна:
     *      - development орчинд SSL verify унтраалттай
     *      - production эсвэл бусад орчинд SSL verify идэвхтэй (аюулгүй)
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
     * @param array $options
     *      Нэмэлт cURL options (жишээ нь: CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1).
     *
     * @return array
     *      JSON decode хийгдсэн хариу массив,
     *      эсвэл:
     *         ['error' => ['code' => ..., 'message' => ...]]
     */
    public function request(string $uri, string $method, array $payload, array $headers, array $options = []): array
    {
        try {
            $header = ['Content-Type: application/json'];

            foreach ($headers as $key => $value) {
                $header[] = "$key: $value";
            }

            // SSL verify тохиргоо: CODESAUR_APP_ENV environment variable-аас уншина
            // development орчинд SSL verify унтраалттай, production орчинд идэвхтэй
            $appEnv = \getenv('CODESAUR_APP_ENV') ?: ($_ENV['CODESAUR_APP_ENV'] ?? $_SERVER['CODESAUR_APP_ENV'] ?? 'production');
            $isDevelopment = \strtolower($appEnv) === 'development';

            // Хэрэглэгчийн өгсөн options-ийг default options-той нэгтгэх
            // array_replace ашигласнаар хэрэглэгчийн options давамгайлна
            $curlOptions = \array_replace([
                \CURLOPT_SSL_VERIFYHOST => !$isDevelopment ? 2 : false,
                \CURLOPT_SSL_VERIFYPEER => !$isDevelopment,
            ], $options);
            $curlOptions[\CURLOPT_HTTPHEADER] = $header;

            $isGet = \strtoupper($method) == 'GET';            
            // GET хүсэлтэд query параметрүүдийг URL-д query string хэлбэрээр нэмнэ
            if ($isGet && !empty($payload)) {
                $queryString = \http_build_query($payload);
                $separator = \strpos($uri, '?') !== false ? '&' : '?';
                $uri = $uri . $separator . $queryString;
                $data = '';
            } else {
                // POST, PUT, DELETE хүсэлтэд JSON body болгон илгээнэ
                $data = empty($payload)
                    ? ($isGet ? '' : '{}')
                    : (\json_encode($payload) ?: throw new \Exception(__CLASS__ . ': Error encoding request payload!'));
            }

            return \json_decode(
                (new CurlClient())->request($uri, $method, $data, $curlOptions),
                true
            ) ?? throw new \Exception(__CLASS__ . ": [$uri] Response json cannot be decoded!");
        } catch (\Throwable $e) {
            return ['error' => ['code' => $e->getCode(), 'message' => $e->getMessage()]];
        }
    }
}
