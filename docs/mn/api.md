# API Documentation

**codesaur/http-client** пакетийн бүрэн API баримт бичиг.

> **Тайлбар:** Энэхүү API баримт бичиг нь Cursor AI-аар үүсгэгдсэн.

---

## Namespace

```
codesaur\Http\Client
```

---

## Classes

### 0. Response

HTTP хариуг обьект хэлбэрээр илэрхийлэх.

#### Description

Энэ класс нь CurlClient-ийн `send()` методоос буцаасан HTTP хариуг илэрхийлнэ. Status code, headers, body-д readonly хандалт олгоно.

#### Constructor

**Signature:**
```php
public function __construct(
    public readonly int $statusCode,
    public readonly array $headers,
    public readonly string $body
)
```

**Parameters:**
- `$statusCode` (int) - HTTP статус код (200, 404, 500 гэх мэт)
- `$headers` (array<string, string>) - Хариуны HTTP headers
- `$body` (string) - Хариуны бие (response body)

#### Properties (readonly)

- `$statusCode` (int) - HTTP статус код
- `$headers` (array) - Хариуны headers
- `$body` (string) - Хариуны бие

#### Methods

##### `json()`
Хариуны body-г JSON массив болгон decode хийх.

**Signature:** `public function json(): ?array`
**Returns:** Decode хийгдсэн массив, эсвэл null (JSON биш бол).

##### `isOk()`
Статус код 2xx (амжилттай) эсэхийг шалгах.

**Signature:** `public function isOk(): bool`
**Returns:** 200-299 хооронд бол true.

##### `isError()`
Статус код 4xx эсвэл 5xx (алдаа) эсэхийг шалгах.

**Signature:** `public function isError(): bool`
**Returns:** 400 ба түүнээс дээш бол true.

##### `getHeader()`
Тодорхой нэртэй header-ийн утгыг авах (том жижиг үсэг ялгахгүй).

**Signature:** `public function getHeader(string $name, ?string $default = null): ?string`
**Parameters:**
- `$name` (string) - Header нэр (жишээ: 'Content-Type')
- `$default` (?string) - Header олдохгүй бол буцаах анхдагч утга

**Returns:** Header-ийн утга эсвэл анхдагч утга.

**Example:**
```php
use codesaur\Http\Client\CurlClient;

$curl = new CurlClient();
$response = $curl->send('https://httpbin.org/get');

echo $response->statusCode;  // 200
echo $response->body;        // хариуны бие
print_r($response->json());  // JSON массив
echo $response->getHeader('Content-Type'); // "application/json"

if ($response->isOk()) {
    // Амжилттай
}
if ($response->isError()) {
    // Алдаатай
}
```

---

### 1. CurlClient

HTTP хүсэлт илгээх зориулалттай хөнгөн жинтэй cURL клиент.

#### Description

Энэ класс нь хүссэн URL рүү дурын HTTP методоор (`GET`, `POST`, `PUT`, `PATCH`, `DELETE` гэх мэт) хүсэлт илгээж, серверийн хариуг текст хэлбэрээр буцаана.

#### Methods

##### `request()`

Өгөгдсөн URL рүү HTTP хүсэлт илгээх үндсэн функц.

**Signature:**
```php
public function request(
    string $uri,
    string $method = 'GET',
    string $data = '',
    array $options = []
): string
```

**Parameters:**
- `$uri` (string) - Хүсэлт илгээх URL эсвэл endpoint
- `$method` (string) - Хэрэглэх HTTP метод (анхдагч - `GET`)
- `$data` (string) - Хүсэлттэй хамт илгээх өгөгдөл. Хэрэв хоосон биш бол `CURLOPT_POSTFIELDS` тохиргоонд автоматаар нэмэгдэнэ
- `$options` (array<int|string, mixed>) - cURL-ийн нэмэлт тохиргоонууд. Жишээ нь:
  - `CURLOPT_TIMEOUT => 10`
  - `CURLOPT_HTTPHEADER => ['Content-Type: application/json']`

**Returns:**
- `string` - Серверийн хариу (response body) зөв гүйцэтгэлтэй үед

**Throws:**
- `\Exception` - cURL гүйцэтгэх явцад алдаа гарвал `Exception` үүсгэнэ. Алдааны код нь `curl_errno()`, алдааны текст нь `curl_error()`-ийн утга байна

**Example:**
```php
use codesaur\Http\Client\CurlClient;

$curl = new CurlClient();

// GET хүсэлт
$response = $curl->request('https://httpbin.org/get', 'GET');
echo $response;

// POST хүсэлт
$response = $curl->request(
    'https://httpbin.org/post',
    'POST',
    'key=value',
    [
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT => 30
    ]
);
```

##### `send()`
HTTP хүсэлт илгээж, Response обьект буцаах.

**Signature:**
```php
public function send(
    string $uri,
    string $method = 'GET',
    string|array $data = '',
    array $options = []
): Response
```
`request()`-тэй адилхан ажилладаг боловч Response обьект буцаана. String эсвэл array өгөгдөл хүлээн авна (array нь файл upload-д multipart/form-data хэлбэрээр).

##### `sendWithRetry()`
Амжилтгүй хүсэлтийг дахин оролдох.

**Signature:**
```php
public function sendWithRetry(
    string $uri,
    string $method = 'GET',
    string|array $data = '',
    array $options = [],
    int $retries = 3,
    int $delayMs = 500
): Response
```
Exponential backoff ашиглан автоматаар дахин оролдоно. 5xx серверийн алдааг дахин оролдох, 4xx клиент алдааг оролдохгүй.

##### `upload()`
Файл upload хийх.

**Signature:**
```php
public function upload(
    string $uri,
    string $filePath,
    string $fieldName = 'file',
    array $fields = [],
    array $options = []
): Response
```
CURLFile ашиглан multipart/form-data хэлбэрээр файл илгээнэ. Файл олдохгүй бол Exception шиднэ.

##### `enableDebug()`
Debug горимыг идэвхжүүлэх/унтраах.

**Signature:** `public function enableDebug(bool $enable = true): static`
Fluent interface буцаана.

##### `getDebugLog()`
Debug лог бичлэгүүдийг авах.

**Signature:** `public function getDebugLog(): array`

##### `clearDebugLog()`
Debug лог бичлэгүүдийг цэвэрлэх.

**Signature:** `public function clearDebugLog(): static`
Fluent interface буцаана.

---

### 2. JSONClient

JSON суурьтай HTTP хүсэлтүүд илгээх зориулалттай клиент.

#### Description

Энэхүү класс нь CurlClient дээр суурилан ажилладаг бөгөөд JSON payload-той GET, POST, PUT, PATCH, DELETE хүсэлтүүдийг хялбараар илгээж, серверийн хариуг PHP массив хэлбэрээр буцаана.

Алдаа гарсан тохиолдолд Exception шидэхийн оронд:
```json
{
  "error": {
    "code": 123,
    "message": "Алдаа үүссэн байна..."
  }
}
```
хэлбэртэй массив буцаана.

#### Constructor

**Signature:** `public function __construct(string $baseUrl = '')`
Суурь URL тохируулах. Бүх хүсэлтийн URI-д автоматаар угтвар болно. Бүтэн URL (http/https) өгвөл суурь URL-г алгасна.

#### Methods

##### `getBaseUrl()`

Одоогийн суурь URL-г авах.

**Signature:** `public function getBaseUrl(): string`

##### `get()`

JSON GET хүсэлт илгээх.

**Signature:**
```php
public function get(string $uri, array $payload = [], array $headers = [], array $options = []): array
```

**Parameters:**
- `$uri` (string) - Хандах URL
- `$payload` (array) - Query string болгон нэмэгдэх өгөгдөл
- `$headers` (array) - Нэмэлт HTTP headers (`name => value` хэлбэр)
- `$options` (array) - Нэмэлт cURL options (жишээ нь: `CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1`)

**Returns:**
- `array` - Decode хийгдсэн JSON хариу эсвэл алдааны бүтэц

**Example:**
```php
use codesaur\Http\Client\JSONClient;

$client = new JSONClient();

$response = $client->get(
    'https://httpbin.org/get',
    ['hello' => 'world'],
    ['Authorization' => 'Bearer token123']
);

print_r($response);

// HTTP/1.1 хувилбар ашиглах (HTTP/2 алдаанаас сэргийлэх)
$response = $client->get(
    'https://httpbin.org/get',
    ['test' => 'value'],
    [],
    [CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1]
);
```

##### `post()`

JSON POST хүсэлт илгээх.

**Signature:**
```php
public function post(string $uri, array $payload, array $headers = [], array $options = []): array
```

**Parameters:**
- `$uri` (string) - Хандах URL
- `$payload` (array) - JSON болгон илгээх өгөгдөл
- `$headers` (array) - Нэмэлт HTTP headers
- `$options` (array) - Нэмэлт cURL options (жишээ нь: `CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1`)

**Returns:**
- `array` - Серверийн JSON хариу

**Example:**
```php
$response = $client->post(
    'https://httpbin.org/post',
    ['test' => 'codesaur'],
    ['X-Custom-Header' => 'value']
);

echo $response['json']['test']; // codesaur

// HTTP/1.1 хувилбар болон timeout тохируулах
$response = $client->post(
    'https://api.example.com/endpoint',
    ['data' => 'value'],
    ['Authorization' => 'Bearer token'],
    [
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_TIMEOUT => 30
    ]
);
```

##### `put()`

JSON PUT хүсэлт илгээх.

**Signature:**
```php
public function put(string $uri, array $payload, array $headers = [], array $options = []): array
```

**Parameters:**
- `$uri` (string) - Хандах URL
- `$payload` (array) - JSON болгон илгээх өгөгдөл
- `$headers` (array) - Нэмэлт HTTP headers
- `$options` (array) - Нэмэлт cURL options (жишээ нь: `CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1`)

**Returns:**
- `array` - Серверийн JSON хариу

**Example:**
```php
$response = $client->put(
    'https://httpbin.org/put',
    ['id' => 1, 'name' => 'Updated Name']
);
```

##### `patch()`

JSON PATCH хүсэлт илгээх. Partial update хийхэд ашиглана.

**Signature:**
```php
public function patch(string $uri, array $payload, array $headers = [], array $options = []): array
```

**Parameters:**
- `$uri` (string) - Хандах URL
- `$payload` (array) - JSON болгон илгээх өгөгдөл
- `$headers` (array) - Нэмэлт HTTP headers
- `$options` (array) - Нэмэлт cURL options (жишээ нь: `CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1`)

**Returns:**
- `array` - Серверийн JSON хариу

**Example:**
```php
$response = $client->patch(
    'https://httpbin.org/patch',
    ['name' => 'Partial Update']
);
```

##### `delete()`

JSON DELETE хүсэлт илгээх.

**Signature:**
```php
public function delete(string $uri, array $payload, array $headers = [], array $options = []): array
```

**Parameters:**
- `$uri` (string) - Хандах URL
- `$payload` (array) - JSON болгон илгээх өгөгдөл
- `$headers` (array) - Нэмэлт HTTP headers
- `$options` (array) - Нэмэлт cURL options (жишээ нь: `CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1`)

**Returns:**
- `array` - Серверийн JSON хариу

**Example:**
```php
$response = $client->delete(
    'https://httpbin.org/delete',
    ['id' => 123]
);
```

##### `request()`

JSON HTTP хүсэлт илгээх үндсэн функц.

**Signature:**
```php
public function request(string $uri, string $method, array $payload, array $headers, array $options = []): array
```

**Parameters:**
- `$uri` (string) - Хүсэлт илгээх URL
- `$method` (string) - HTTP метод (GET, POST, PUT, PATCH, DELETE)
- `$payload` (array) - Илгээх өгөгдөл
- `$headers` (array) - Нэмэлт headers (`key => value`)
- `$options` (array) - Нэмэлт cURL options (жишээ нь: `CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1`)

**Returns:**
- `array` - JSON decode хийгдсэн хариу массив, эсвэл:
  ```php
  ['error' => ['code' => ..., 'message' => ...]]
  ```

**Features:**
- Payload-ийг автоматаар JSON болгоно
- Content-Type: application/json header автоматаар нэмнэ
- SSL verify нь `CODESAUR_APP_ENV` environment variable-аас хамаарна:
  - `development` орчинд SSL verify унтраалттай
  - `production` эсвэл бусад орчинд SSL verify идэвхтэй (аюулгүй)
- JSON decode алдааг шалгана
- Бүх алдааг нэг мөрөөр 'error' бүтэц болгон буцаана
- Нэмэлт cURL options дамжуулж болно (жишээ нь: `CURLOPT_HTTP_VERSION`, `CURLOPT_TIMEOUT`)

**Environment Configuration:**
```bash
# .env файл эсвэл environment variable
CODESAUR_APP_ENV=development  # эсвэл production
```

**cURL Options:**

Боломжит HTTP хувилбарын утгууд:

| Constant | Утга | Тайлбар |
|----------|------|---------|
| `CURL_HTTP_VERSION_NONE` | 0 | cURL-д өөрөө сонгуулах |
| `CURL_HTTP_VERSION_1_0` | 1 | HTTP/1.0 |
| `CURL_HTTP_VERSION_1_1` | 2 | HTTP/1.1 |
| `CURL_HTTP_VERSION_2_0` | 3 | HTTP/2 |
| `CURL_HTTP_VERSION_2TLS` | 4 | HTTP/2 (HTTPS-д зөвхөн) |
| `CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE` | 5 | HTTP/2 negotiate-гүй |

---

### 3. Mail

PHP-ийн `mail()` функцийг ашиглан MIME стандарттай, хавсралт бүхий эсвэл энгийн имэйл илгээхэд зориулагдсан хөнгөн жинтэй имэйл клиент класс.

#### Description

**Онцлогууд:**
- To / Cc / Bcc хүлээн авагчид удирдах
- Элгэн (inline) болон олон төрлийн хавсралт дэмжих:
  - file path -> `addFileAttachment()`
  - URL -> `addUrlAttachment()`
  - raw content -> `addContentAttachment()`
- HTML болон plaintext имэйл илгээх
- UTF-8 encoded header & filename бүрэн дэмжлэг
- MIME multipart имэйл автоматаар үүсгэнэ

#### Properties

- `$_recipients` (private array) - Хүлээн авагчдын жагсаалт (To, Cc, Bcc)
- `$from` (protected string) - Илгээгчийн имэйл хаяг
- `$fromName` (protected string) - Илгээгчийн нэр
- `$replyTo` (protected string) - Хариу илгээх имэйл
- `$replyToName` (protected string) - Хариу илгээх нэр
- `$subject` (protected string) - Имэйл гарчиг
- `$message` (protected string) - Имэйл мессежийн гол агуулга
- `$_attachments` (private array) - Хавсралтын жагсаалт

#### Methods

##### `targetTo()`

Олон хүлээн авагчийг reset хийж, зөвхөн нэг шинэ recipient оноох.

**Signature:**
```php
public function targetTo(string $email, string $name = ''): self
```

**Parameters:**
- `$email` (string) - Хүлээн авагчийн имэйл хаяг
- `$name` (string) - Хүлээн авагчийн нэр (сонголттой)

**Returns:**
- `self` - Fluent interface-ийн зориулалтаар объектыг буцаана

**Example:**
```php
use codesaur\Http\Client\Mail;

$mail = new Mail();
$mail->targetTo('user@example.com', 'Хэрэглэгч');
```

##### `addRecipient()`

To хүлээн авагч нэмэх.

**Signature:**
```php
public function addRecipient(string $email, string $name = ''): self
```

**Parameters:**
- `$email` (string) - Хүлээн авагчийн имэйл хаяг
- `$name` (string) - Хүлээн авагчийн нэр (сонголттой)

**Returns:**
- `self` - Fluent interface-ийн зориулалтаар объектыг буцаана

**Example:**
```php
$mail->addRecipient('user1@example.com', 'Хүн 1');
$mail->addRecipient('user2@example.com', 'Хүн 2');
```

##### `addCCRecipient()`

Cc хүлээн авагч нэмэх.

**Signature:**
```php
public function addCCRecipient(string $email, string $name = ''): self
```

**Parameters:**
- `$email` (string) - Cc хүлээн авагчийн имэйл хаяг
- `$name` (string) - Cc хүлээн авагчийн нэр (сонголттой)

**Returns:**
- `self` - Fluent interface-ийн зориулалтаар объектыг буцаана

**Example:**
```php
$mail->addCCRecipient('cc@example.com', 'CC Хүлээн авагч');
```

##### `addBCCRecipient()`

Bcc хүлээн авагч нэмэх.

**Signature:**
```php
public function addBCCRecipient(string $email, string $name = ''): self
```

**Parameters:**
- `$email` (string) - Bcc хүлээн авагчийн имэйл хаяг
- `$name` (string) - Bcc хүлээн авагчийн нэр (сонголттой)

**Returns:**
- `self` - Fluent interface-ийн зориулалтаар объектыг буцаана

**Example:**
```php
$mail->addBCCRecipient('bcc@example.com', 'BCC Хүлээн авагч');
```

##### `addRecipients()`

Олон хүлээн авагчийг массив хэлбэрээр нэмэх.

**Signature:**
```php
public function addRecipients(array $recipients): self
```

**Parameters:**
- `$recipients` (array) - Хүлээн авагчдын массив: `['To'=>[], 'Cc'=>[], 'Bcc'=>[]]`
  - Жишээ: `['To' => [['email' => 'user@example.com', 'name' => 'Нэр']]]`

**Returns:**
- `self` - Fluent interface-ийн зориулалтаар объектыг буцаана

**Example:**
```php
$mail->addRecipients([
    'To' => [
        ['email' => 'user1@example.com', 'name' => 'Нэр 1'],
        ['email' => 'user2@example.com', 'name' => 'Нэр 2']
    ],
    'Cc' => [
        ['email' => 'cc@example.com', 'name' => 'CC']
    ],
    'Bcc' => [
        ['email' => 'bcc@example.com']
    ]
]);
```

##### `getRecipients()`

Тодорхой төрлийн хүлээн авагчдын жагсаалтыг буцаах.

**Signature:**
```php
public function getRecipients(string $type): array
```

**Parameters:**
- `$type` (string) - Хүлээн авагчийн төрөл ('To', 'Cc', 'Bcc')

**Returns:**
- `array` - Хүлээн авагчдын жагсаалт, эсвэл хоосон массив

**Example:**
```php
$toRecipients = $mail->getRecipients('To');
// [
//     ['email' => 'user1@example.com', 'name' => 'Нэр 1'],
//     ['email' => 'user2@example.com']
// ]
```

##### `setSubject()`

Имэйлийн гарчиг тохируулах.

**Signature:**
```php
public function setSubject(string $subject): self
```

**Parameters:**
- `$subject` (string) - Имэйлийн гарчиг

**Returns:**
- `self` - Fluent interface-ийн зориулалтаар объектыг буцаана

**Example:**
```php
$mail->setSubject('Сайн байна уу?');
```

##### `setMessage()`

Имэйлийн гол агуулга тохируулах.

**Signature:**
```php
public function setMessage(string $message): self
```

**Parameters:**
- `$message` (string) - Имэйлийн агуулга (HTML эсвэл энгийн текст)

**Returns:**
- `self` - Fluent interface-ийн зориулалтаар объектыг буцаана

**Example:**
```php
$mail->setMessage('<h1>Hello!</h1><p>Тест имэйл.</p>');
```

##### `setFrom()`

Илгээгчийн мэдээлэл тохируулах.

**Signature:**
```php
public function setFrom(string $email, string $name = ''): self
```

**Parameters:**
- `$email` (string) - Илгээгчийн имэйл хаяг
- `$name` (string) - Илгээгчийн нэр (сонголттой)

**Returns:**
- `self` - Fluent interface-ийн зориулалтаар объектыг буцаана

**Throws:**
- `\InvalidArgumentException` - Имэйл хаяг буруу байвал

**Example:**
```php
$mail->setFrom('no-reply@example.com', 'codesaur');
```

##### `setReplyTo()`

Reply-To тохируулах.

**Signature:**
```php
public function setReplyTo(string $email, string $name = ''): self
```

**Parameters:**
- `$email` (string) - Хариу илгээх имэйл хаяг
- `$name` (string) - Хариу илгээх нэр (сонголттой)

**Returns:**
- `self` - Fluent interface-ийн зориулалтаар объектыг буцаана

**Throws:**
- `\InvalidArgumentException` - Имэйл хаяг буруу байвал

**Example:**
```php
$mail->setReplyTo('support@example.com', 'Support Team');
```

##### `addAttachment()`

Хавсралт нэмж өгөгдсөн төрөлд үндэслэн чиглүүлэх.

**Signature:**
```php
public function addAttachment(array $attachment): self
```

**Parameters:**
- `$attachment` (array) - Хавсралтын массив:
  - `['path' => '/path/to/file']` - файлын замаар
  - `['url' => 'https://example.com/file']` - URL-аар
  - `['content' => '...', 'name' => 'file.txt']` - агуулгаар

**Returns:**
- `self` - Fluent interface-ийн зориулалтаар объектыг буцаана

**Throws:**
- `\InvalidArgumentException` - Хавсралтын мэдээлэл буруу байвал

**Example:**
```php
$mail->addAttachment(['path' => '/path/to/file.pdf']);
$mail->addAttachment(['url' => 'https://example.com/logo.png']);
$mail->addAttachment(['content' => 'Hello world', 'name' => 'note.txt']);
```

##### `addFileAttachment()`

Файлын замаар хавсралт нэмэх.

**Signature:**
```php
public function addFileAttachment(string $filePath): self
```

**Parameters:**
- `$filePath` (string) - Хавсралт болгох файлын зам

**Returns:**
- `self` - Fluent interface-ийн зориулалтаар объектыг буцаана

**Throws:**
- `\InvalidArgumentException` - Файл олдсонгүй эсвэл буруу байвал

**Example:**
```php
$mail->addFileAttachment(__DIR__ . '/file.pdf');
```

##### `addUrlAttachment()`

URL дээрх файлыг хавсралтад нэмэх.

**Signature:**
```php
public function addUrlAttachment(string $fileUrl): self
```

**Parameters:**
- `$fileUrl` (string) - Хавсралт болгох файлын URL

**Returns:**
- `self` - Fluent interface-ийн зориулалтаар объектыг буцаана

**Throws:**
- `\InvalidArgumentException` - URL хүчинтэй биш эсвэл файл олдсонгүй бол

**Example:**
```php
$mail->addUrlAttachment('https://example.com/logo.png');
```

##### `addContentAttachment()`

Raw binary өгөгдлөөр хавсралт нэмэх.

**Signature:**
```php
public function addContentAttachment(string $fileContent, string $fileName): self
```

**Parameters:**
- `$fileContent` (string) - Хавсралтын агуулга (binary эсвэл текст)
- `$fileName` (string) - Хавсралтын файлын нэр

**Returns:**
- `self` - Fluent interface-ийн зориулалтаар объектыг буцаана

**Throws:**
- `\InvalidArgumentException` - Агуулга эсвэл файлын нэр хоосон байвал

**Example:**
```php
$mail->addContentAttachment("Hello world", "note.txt");
```

##### `clearAttachments()`

Хавсралтуудыг хоослох.

**Signature:**
```php
public function clearAttachments(): void
```

**Example:**
```php
$mail->clearAttachments();
```

##### `getAttachments()`

Хавсралтын жагсаалт буцаах.

**Signature:**
```php
public function getAttachments(): array
```

**Returns:**
- `array` - Хавсралтын жагсаалт, эсвэл хоосон массив

**Example:**
```php
$attachments = $mail->getAttachments();
```

##### `sendMail()`

Имэйл илгээх үндсэн функц.

**Signature:**
```php
public function sendMail(): bool
```

**Returns:**
- `bool` - Имэйл амжилттай илгээгдсэн эсэх

**Throws:**
- `\InvalidArgumentException` - Хүлээн авагч, илгээгч, гарчиг эсвэл мессеж тохируулаагүй байвал
- `\RuntimeException` - Илгээх явцад алдаа гарвал

**Description:**
MIME multipart имэйл үүсгэж, хавсралттай/хавсралтгүй илгээнэ.

**Example:**
```php
$mail = new Mail();

$mail->targetTo('user@example.com', 'Хэрэглэгч');
$mail->setFrom('no-reply@example.com', 'codesaur');
$mail->setSubject('Сайн байна уу?');
$mail->setMessage('<h1>Hello!</h1><p>Тест имэйл.</p>');
$mail->addFileAttachment(__DIR__ . '/file.pdf');

$mail->sendMail();
```

---

## Fluent Interface

Mail класс нь fluent interface дэмжиж байна, учир нь олон method-ууд `$this` буцаана:

```php
$mail = new Mail();

$mail
    ->targetTo('user@example.com', 'Хэрэглэгч')
    ->setFrom('no-reply@example.com', 'codesaur')
    ->setSubject('Сайн байна уу?')
    ->setMessage('<h1>Hello!</h1><p>Тест имэйл.</p>')
    ->addFileAttachment(__DIR__ . '/file.pdf')
    ->addRecipient('cc@example.com', 'CC')
    ->sendMail();
```

---

## Exceptions

### CurlClient

- `\Exception` - cURL гүйцэтгэх явцад алдаа гарвал

### JSONClient

Алдаа гарсан тохиолдолд Exception шидэхийн оронд алдааны бүтэц буцаана:
```php
['error' => ['code' => ..., 'message' => ...]]
```

### Mail

- `\InvalidArgumentException` - Имэйл хаяг буруу, файл олдсонгүй, шаардлагатай утга тохируулаагүй байвал
- `\RuntimeException` - Имэйл илгээх явцад алдаа гарвал

---

## Notes

1. **SSL Verification:** JSONClient нь `CODESAUR_APP_ENV` environment variable-аас хамааруулан SSL verify-ийг тохируулна:
   - `development` -> SSL verify унтраалттай
   - `production` эсвэл бусад -> SSL verify идэвхтэй (default)

2. **UTF-8 Support:** Mail класс нь UTF-8 бүрэн дэмжлэгтэй (нэрс, файлын нэр, гарчиг г.м.)

3. **MIME Multipart:** Mail класс нь хавсралттай үед автоматаар MIME multipart имэйл үүсгэнэ

4. **Error Handling:** JSONClient нь бүх алдааг нэг мөрөөр 'error' бүтэц болгон буцаана, Exception шидэхгүй

5. **Response обьект:** CurlClient-ийн `send()` метод нь `Response` обьект буцаана - `statusCode`, `headers`, `body` шинж чанар, `json()`, `isOk()`, `isError()`, `getHeader()` методууд

6. **Дахин оролдох:** `sendWithRetry()` нь exponential backoff ашиглана (500ms, 1000ms, 2000ms...), зөвхөн 5xx серверийн алдааг дахин оролдоно

7. **Файл upload:** `upload()` нь CURLFile ашиглан multipart/form-data хэлбэрээр файл илгээнэ

8. **Debug горим:** `enableDebug(true)` ашиглан бүх хүсэлт/хариуны мэдээллийг лог хийнэ

---

## License

MIT License

---

**API Documentation Generated by:** Cursor AI
**Last Updated:** 2026
