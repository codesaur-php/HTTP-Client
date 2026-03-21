# codesaur/http-client

A lightweight, object-oriented HTTP client component for sending HTTP requests and processing/sending MIME emails.

---

## Key Features

- **CurlClient** - Flexible HTTP client based on cURL
- **JSONClient** - Convenient for working with REST APIs with JSON data
- **Response** - HTTP response object with status code, headers, and body
- **Mail** - MIME email sender with HTML + Text + multiple attachments
- Full UTF-8 support (names, file names, headers, etc.)  
- Lightweight, fast, can be used on any framework or standalone  
- Only requires `ext-curl` and `ext-json`  

---

## Installation

```bash
composer require codesaur/http-client
```

---

# 1. CurlClient - General HTTP Client

```php
use codesaur\Http\Client\CurlClient;

$curl = new CurlClient();

$response = $curl->request(
    'https://httpbin.org/get',
    'GET'
);

echo $response;
```

### Response Object

```php
use codesaur\Http\Client\CurlClient;

$curl = new CurlClient();

// send() returns a Response object
$response = $curl->send('https://httpbin.org/get');

echo $response->statusCode;    // 200
echo $response->body;          // raw response body
print_r($response->headers);   // response headers array
print_r($response->json());    // decoded JSON array

$response->isOk();    // true (2xx status code)
$response->isError(); // false (not 4xx/5xx)
$response->getHeader('Content-Type'); // "application/json"
```

### File Upload

```php
$response = $curl->upload(
    'https://httpbin.org/post',
    '/path/to/file.pdf',
    'document',
    ['description' => 'My file']
);
```

### Retry with Exponential Backoff

```php
$response = $curl->sendWithRetry(
    'https://api.example.com/data',
    'GET',
    '',
    [],
    3,    // retries
    500   // initial delay in ms
);
```

### Debug Mode

```php
$curl->enableDebug(true);
$curl->send('https://httpbin.org/get');
print_r($curl->getDebugLog());
$curl->clearDebugLog();
```

---

# 2. JSONClient - Working with JSON APIs

**Note:** JSONClient configures SSL verification based on the `CODESAUR_APP_ENV` environment variable:
- SSL verification is disabled in `development` environment (suitable for development)
- SSL verification is enabled in `production` or other environments (secure)

```bash
# .env file or environment variable
CODESAUR_APP_ENV=development  # or production
```

```php
// With base URL
$client = new JSONClient('https://api.example.com/v1');
$response = $client->get('/users'); // https://api.example.com/v1/users

// Without base URL (direct URLs)
$client = new JSONClient();
```

### GET Request

```php
use codesaur\Http\Client\JSONClient;

$client = new JSONClient();

$response = $client->get(
    'https://httpbin.org/get',
    ['hello' => 'world']
);

print_r($response);
```

### POST Request

```php
$response = $client->post(
    'https://httpbin.org/post',
    ['test' => 'codesaur']
);

echo $response['json']['test']; // codesaur
```

### PATCH Request

```php
$response = $client->patch(
    'https://httpbin.org/patch',
    ['status' => 'active']
);
```

### Error Response Structure

```json
{
  "error": { "code": 123, "message": "An error occurred..." }
}
```

---

# 3. Mail - MIME HTML + Attachment Email Client

### Sending Simple HTML Email

```php
use codesaur\Http\Client\Mail;

$mail = new Mail();

$mail->targetTo('user@example.com', 'User');
$mail->setFrom('no-reply@example.com', 'codesaur');
$mail->setSubject('Hello!');
$mail->setMessage('<h1>Hello!</h1><p>Test email.</p>');

$mail->sendMail();
```

### Adding Attachments

```php
$mail->addFileAttachment(__DIR__ . '/file.pdf');
$mail->addUrlAttachment('https://example.com/logo.png');
$mail->addContentAttachment("Hello world", "note.txt");
```

### Multiple Recipients

```php
$mail->addRecipient('a@example.com', 'Person A');
$mail->addCCRecipient('b@example.com', 'Person B');
$mail->addBCCRecipient('c@example.com', 'Person C');
```

---

## Running Tests

### Composer Test Commands

```bash
# Run all tests (Unit + Integration tests)
composer test

# Run only Unit tests
composer test:unit

# Run only Integration tests
composer test:integration

# Run Unit and Integration tests sequentially
composer test:all

# Generate HTML coverage report (in coverage/ directory)
composer test:coverage
```

### Test Information

- **Unit Tests**: Tests for CurlClient, JSONClient, Mail classes
- **ResponseTest**: Tests for Response class (statusCode, headers, body, json, isOk, isError)
- **Integration Tests**: Integration tests working with real APIs
- **EndToEndTest**: End-to-end test using all components together

### Using PHPUnit Directly

Instead of Composer commands, you can run PHPUnit directly:

```bash
# Run all tests
vendor/bin/phpunit

# Only Unit tests
vendor/bin/phpunit --testsuite Unit

# Only Integration tests
vendor/bin/phpunit --testsuite Integration

# Coverage report (Clover XML format)
vendor/bin/phpunit --coverage-clover coverage/clover.xml

# Coverage report (HTML format)
vendor/bin/phpunit --coverage-html coverage
```
**Windows users:** Replace `vendor/bin/phpunit` with `vendor\bin\phpunit.bat`

---

# CI/CD Pipeline

This project has a CI/CD pipeline using GitHub Actions:

- **Automated Tests** - Tests run on Push or Pull Request
- **Multiple PHP Versions** - Tests on PHP 8.2, 8.3
- **Multiple OS** - Tests on Ubuntu and Windows
- **Code Coverage** - Generates coverage information on Pull Request
- **Security Check** - Runs Composer audit
- **Code Linting** - Checks PHP syntax

CI/CD pipeline details: [.github/workflows/ci.yml](.github/workflows/ci.yml)

---

# License

This project is licensed under the MIT License.

---

# Author

Narankhuu  
https://github.com/codesaur
