# codesaur/http-client

[![CI](https://github.com/codesaur-php/HTTP-Client/actions/workflows/ci.yml/badge.svg)](https://github.com/codesaur-php/HTTP-Client/actions)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2.1-777BB4.svg?logo=php)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

HTTP хүсэлт илгээх болон MIME имэйл боловсруулах/илгээх энгийн хөнгөн жинтэй, объект хандалтат http-client компонент.

---

## Агуулга / Table of Contents

1. [Монгол](#1-монгол-тайлбар) | 2. [English](#2-english-description) | 3. [Getting Started](#3-getting-started)

---

## 1. Монгол тайлбар

`codesaur/http-client` нь **codesaur ecosystem**-ийн нэг хэсэг бөгөөд хөнгөн жинтэй,
фрэймворкоос үл хамааран standalone байдлаар ашиглаж болох PHP HTTP клиент компонент юм.

Багц нь дараах 4 үндсэн class-аас бүрдэнэ:

- **CurlClient** - cURL дээр суурилсан уян хатан HTTP клиент
- **JSONClient** - JSON өгөгдөлтэй REST API-тэй ажиллахад тохиромжтой
- **Response** - HTTP хариуг обьект хэлбэрээр илэрхийлэх (status code, headers, body)
- **Mail** - HTML + Text + олон хавсралттай MIME имэйл илгээгч  

### Онцлох боломжууд

- UTF-8 бүрэн дэмжлэг (нэрс, файлын нэр, гарчиг г.м.)  
- Хөнгөн, хурдан, ямар ч фрэймворк дээр эсвэл дангаар ашиглаж болно  
- Зөвхөн `ext-curl`, `ext-json` байхад л болно  

### Дэлгэрэнгүй мэдээлэл

- [Бүрэн танилцуулга](docs/mn/README.md) - Суурилуулалт, хэрэглээ, жишээнүүд
- [API тайлбар](docs/mn/api.md) - Бүх метод, exception-үүдийн тайлбар
- [Шалгалтын тайлан](docs/mn/review.md) - Код шалгалтын тайлан

---

## 2. English description

`codesaur/http-client` is part of the **codesaur ecosystem** and is a lightweight PHP HTTP client component that can be used standalone, independent of any framework.

The package consists of the following 4 core classes:

- **CurlClient** - flexible HTTP client based on cURL
- **JSONClient** - convenient for working with REST APIs with JSON data
- **Response** - HTTP response object with status code, headers, and body
- **Mail** - MIME email sender with HTML + Text + multiple attachments  

### Key Features

- Full UTF-8 support (names, file names, headers, etc.)  
- Lightweight, fast, can be used on any framework or standalone  
- Only requires `ext-curl` and `ext-json`  

### Documentation

- [Full Documentation](docs/en/README.md) - Installation, usage, examples
- [API Reference](docs/en/api.md) - Complete API documentation
- [Review](docs/en/review.md) - Complete package review and code quality assessment

---

## 3. Getting Started

### Requirements

- PHP **8.2.1+**
- Composer
- `ext-curl` extension
- `ext-json` extension

### Installation

Composer ашиглан суулгана / Install via Composer:

```bash
composer require codesaur/http-client
```

### Quick Examples

#### CurlClient - Ерөнхий HTTP клиент

```php
use codesaur\Http\Client\CurlClient;

// CurlClient үүсгэх / Create CurlClient instance
$curl = new CurlClient();

// GET хүсэлт илгээх / Send GET request
$response = $curl->request(
    'https://httpbin.org/get',
    'GET'
);

// Response обьект авах / Get Response object
$res = $curl->send('https://httpbin.org/get');
echo $res->statusCode; // 200
echo $res->getHeader('Content-Type'); // application/json
print_r($res->json()); // decoded JSON array

// Файл upload хийх / Upload file
$res = $curl->upload('https://httpbin.org/post', '/path/to/file.pdf');

// Дахин оролдох / Retry on failure
$res = $curl->sendWithRetry('https://httpbin.org/get', retries: 3);

// Debug горим / Debug mode
$curl->enableDebug(true);
$curl->send('https://httpbin.org/get');
print_r($curl->getDebugLog());

// Хариуг хэвлэх / Print response
echo $response;
```

#### JSONClient - JSON API-тэй ажиллах

```php
use codesaur\Http\Client\JSONClient;

// JSONClient үүсгэх / Create JSONClient instance
$client = new JSONClient();

// GET хүсэлт илгээх / Send GET request
$response = $client->get(
    'https://httpbin.org/get',
    ['hello' => 'world']
);

// POST хүсэлт илгээх / Send POST request
$response = $client->post(
    'https://httpbin.org/post',
    ['test' => 'codesaur']
);

// HTTP/1.1 хувилбар ашиглах (HTTP/2 алдаанаас сэргийлэх)
// Use HTTP/1.1 version (to prevent HTTP/2 errors)
$response = $client->post(
    'https://api.example.com/endpoint',
    ['data' => 'value'],
    ['Authorization' => 'Bearer token'],
    [CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1]
);

// Base URL ашиглах / Use Base URL
$api = new JSONClient('https://api.example.com/v1');
$users = $api->get('/users');

// PATCH хүсэлт / PATCH request (partial update)
$response = $client->patch(
    'https://httpbin.org/patch',
    ['status' => 'active']
);

// Хариуг хэвлэх / Print response
print_r($response);
```

#### Mail - MIME HTML + Хавсралттай имэйл клиент

```php
use codesaur\Http\Client\Mail;

// Mail үүсгэх / Create Mail instance
$mail = new Mail();

// Хүлээн авагч тохируулах / Set recipient
$mail->targetTo('user@example.com', 'Хэрэглэгч');
// Илгээгч тохируулах / Set sender
$mail->setFrom('no-reply@example.com', 'codesaur');
// Гарчиг тохируулах / Set subject
$mail->setSubject('Сайн байна уу?');
// Зурвас тохируулах / Set message
$mail->setMessage('<h1>Hello!</h1><p>Тест имэйл.</p>');

// Файл хавсралт нэмэх / Add file attachment
$mail->addFileAttachment(__DIR__ . '/file.pdf');
// URL-аас хавсралт нэмэх / Add attachment from URL
$mail->addUrlAttachment('https://example.com/logo.png');

// Имэйл илгээх / Send email
$mail->sendMail();
```

### Running Tests

Тест ажиллуулах / Run tests:

```bash
# Бүх тестүүдийг ажиллуулах / Run all tests
composer test

# Зөвхөн unit тест / Unit tests only
composer test:unit

# Зөвхөн integration тест / Integration tests only
composer test:integration

# Coverage-тэй тест ажиллуулах / Run tests with coverage
composer test:coverage
```

---

## Changelog

- [CHANGELOG.md](CHANGELOG.md) - Full version history

## Contributing & Security

- [Contributing Guide](.github/CONTRIBUTING.md)
- [Security Policy](.github/SECURITY.md)

## License

This project is licensed under the MIT License.

## Author

**Narankhuu**  
codesaur@gmail.com  
https://github.com/codesaur

**codesaur ecosystem:** https://codesaur.net
