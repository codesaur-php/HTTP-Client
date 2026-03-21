<?php

namespace codesaur\Http\Client\Example;

/**
 * codesaur/http-client пакетийн бүх боломжуудыг харуулах жишээ скрипт.
 *
 * Энэ скрипт нь дараах боломжуудыг харуулна:
 *  - CurlClient: request(), send(), upload(), sendWithRetry(), debug горим
 *  - JSONClient: GET, POST, PUT, PATCH, DELETE, Base URL
 *  - Response обьект: statusCode, headers, body, json(), isOk(), isError()
 *
 * httpbin.org нь туршилтын зориулалттай, олон жил тогтвортой ажилласан
 * API тул хөгжүүлэлтийн үеийн жишээ болгон ашиглахад тохиромжтой.
 *
 * @since v2.1.0
 */

\ini_set('display_errors', 'On');
\error_reporting(E_ALL);

require_once '../vendor/autoload.php';

\header('Content-Type: text/html; charset=utf-8');

use codesaur\Http\Client\CurlClient;
use codesaur\Http\Client\JSONClient;

echo '<h1>codesaur/http-client v2.1.0 Examples</h1>';

// ============================================================
// 1. CurlClient - request() (string буцаана)
// ============================================================
echo '<h2>1. CurlClient::request() - String response</h2>';

$curl = new CurlClient();

$body = $curl->request('https://httpbin.org/get', 'GET');
echo '<pre>' . \htmlspecialchars($body) . '</pre>';

// ============================================================
// 2. CurlClient - send() (Response обьект буцаана)
// ============================================================
echo '<h2>2. CurlClient::send() - Response object</h2>';

$response = $curl->send('https://httpbin.org/get');

echo '<ul>';
echo '<li><strong>Status Code:</strong> ' . $response->statusCode . '</li>';
echo '<li><strong>isOk():</strong> ' . ($response->isOk() ? 'true' : 'false') . '</li>';
echo '<li><strong>isError():</strong> ' . ($response->isError() ? 'true' : 'false') . '</li>';
echo '<li><strong>Content-Type:</strong> ' . $response->getHeader('Content-Type', 'unknown') . '</li>';
echo '</ul>';
echo '<p><strong>json():</strong></p>';
echo '<pre>' . \htmlspecialchars(\json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';

// ============================================================
// 3. CurlClient - send() POST
// ============================================================
echo '<h2>3. CurlClient::send() - POST request</h2>';

$response = $curl->send(
    'https://httpbin.org/post',
    'POST',
    'name=codesaur&version=2.1'
);

echo '<p>Status: ' . $response->statusCode . '</p>';
echo '<pre>' . \htmlspecialchars(\json_encode($response->json(), JSON_PRETTY_PRINT)) . '</pre>';

// ============================================================
// 4. CurlClient - 404 хариу шалгах
// ============================================================
echo '<h2>4. CurlClient::send() - 404 Response</h2>';

$response = $curl->send('https://httpbin.org/status/404');

echo '<ul>';
echo '<li><strong>Status Code:</strong> ' . $response->statusCode . '</li>';
echo '<li><strong>isOk():</strong> ' . ($response->isOk() ? 'true' : 'false') . '</li>';
echo '<li><strong>isError():</strong> ' . ($response->isError() ? 'true' : 'false') . '</li>';
echo '</ul>';

// ============================================================
// 5. CurlClient - Debug горим
// ============================================================
echo '<h2>5. CurlClient - Debug mode</h2>';

$curl->enableDebug(true);
$curl->send('https://httpbin.org/get');

$log = $curl->getDebugLog();
echo '<p>Debug log (1 entry):</p>';
echo '<pre>';
foreach ($log as $entry) {
    echo 'URI: ' . $entry['uri'] . "\n";
    echo 'Method: ' . $entry['method'] . "\n";
    echo 'Status: ' . $entry['status'] . "\n";
    echo 'Total time: ' . $entry['curl_info']['total_time'] . "s\n";
}
echo '</pre>';

$curl->clearDebugLog();
$curl->enableDebug(false);

// ============================================================
// 6. CurlClient - sendWithRetry()
// ============================================================
echo '<h2>6. CurlClient::sendWithRetry() - Retry with backoff</h2>';

$response = $curl->sendWithRetry(
    'https://httpbin.org/get',
    'GET',
    '',
    [],
    3,    // retries
    200   // delayMs
);

echo '<p>Status: ' . $response->statusCode . ' (retries available: 3)</p>';

// ============================================================
// 7. CurlClient - upload()
// ============================================================
echo '<h2>7. CurlClient::upload() - File upload</h2>';

// Түр файл үүсгээд upload хийх
$tempFile = \tempnam(\sys_get_temp_dir(), 'example_');
\file_put_contents($tempFile, 'codesaur HTTP Client upload test!');

$response = $curl->upload(
    'https://httpbin.org/post',
    $tempFile,
    'document',
    ['description' => 'Test upload', 'version' => '2.1.0']
);

\unlink($tempFile);

echo '<p>Status: ' . $response->statusCode . '</p>';
$json = $response->json();
echo '<p>Uploaded file field: <code>document</code></p>';
echo '<p>Extra fields: description=' . ($json['form']['description'] ?? 'N/A')
    . ', version=' . ($json['form']['version'] ?? 'N/A') . '</p>';

// ============================================================
// 8. JSONClient - Base URL + GET
// ============================================================
echo '<h2>8. JSONClient - Base URL + GET</h2>';

$client = new JSONClient('https://httpbin.org');

$response = $client->get('/get', ['source' => 'baseurl', 'version' => '2.1.0']);

if (!isset($response['error'])) {
    echo '<p>Base URL: <code>' . $client->getBaseUrl() . '</code></p>';
    echo '<p>args: </p>';
    echo '<pre>' . \htmlspecialchars(\json_encode($response['args'], JSON_PRETTY_PRINT)) . '</pre>';
} else {
    echo '<p>Error: ' . $response['error']['message'] . '</p>';
}

// ============================================================
// 9. JSONClient - POST
// ============================================================
echo '<h2>9. JSONClient::post()</h2>';

$response = $client->post('/post', [
    'name' => 'codesaur',
    'type' => 'http-client',
    'version' => '2.1.0'
]);

if (!isset($response['error'])) {
    echo '<pre>' . \htmlspecialchars(\json_encode($response['json'], JSON_PRETTY_PRINT)) . '</pre>';
} else {
    echo '<p>Error: ' . $response['error']['message'] . '</p>';
}

// ============================================================
// 10. JSONClient - PATCH (partial update)
// ============================================================
echo '<h2>10. JSONClient::patch() - Partial update</h2>';

$response = $client->patch('/patch', [
    'status' => 'active',
    'updated_at' => \date('Y-m-d H:i:s')
]);

if (!isset($response['error'])) {
    echo '<p>Sent partial update:</p>';
    echo '<pre>' . \htmlspecialchars(\json_encode($response['json'], JSON_PRETTY_PRINT)) . '</pre>';
} else {
    echo '<p>Error: ' . $response['error']['message'] . '</p>';
}

// ============================================================
// 11. JSONClient - PUT
// ============================================================
echo '<h2>11. JSONClient::put() - Full update</h2>';

$response = $client->put('/put', [
    'id' => 1,
    'name' => 'Updated Resource',
    'status' => 'active'
]);

if (!isset($response['error'])) {
    echo '<pre>' . \htmlspecialchars(\json_encode($response['json'], JSON_PRETTY_PRINT)) . '</pre>';
} else {
    echo '<p>Error: ' . $response['error']['message'] . '</p>';
}

// ============================================================
// 12. JSONClient - DELETE
// ============================================================
echo '<h2>12. JSONClient::delete()</h2>';

$response = $client->delete('/delete', ['id' => 123]);

if (!isset($response['error'])) {
    echo '<p>Deleted resource with payload:</p>';
    echo '<pre>' . \htmlspecialchars(\json_encode($response['json'], JSON_PRETTY_PRINT)) . '</pre>';
} else {
    echo '<p>Error: ' . $response['error']['message'] . '</p>';
}

// ============================================================
// 13. JSONClient - Full URL overrides Base URL
// ============================================================
echo '<h2>13. JSONClient - Full URL overrides Base URL</h2>';

$response = $client->get('https://httpbin.org/headers', [], [
    'X-Custom-Header' => 'codesaur',
    'Authorization' => 'Bearer example-token'
]);

if (!isset($response['error'])) {
    echo '<p>Base URL: <code>' . $client->getBaseUrl() . '</code> (bypassed by full URL)</p>';
    echo '<pre>' . \htmlspecialchars(\json_encode($response['headers'], JSON_PRETTY_PRINT)) . '</pre>';
} else {
    echo '<p>Error: ' . $response['error']['message'] . '</p>';
}

// ============================================================
// 14. JSONClient - cURL options (HTTP/1.1, timeout)
// ============================================================
echo '<h2>14. JSONClient - cURL options</h2>';

$response = $client->get(
    '/get',
    ['test' => 'curl-options'],
    [],
    [
        \CURLOPT_HTTP_VERSION => \CURL_HTTP_VERSION_1_1,
        \CURLOPT_TIMEOUT => 30
    ]
);

if (!isset($response['error'])) {
    echo '<p>Request with HTTP/1.1 and 30s timeout:</p>';
    echo '<pre>' . \htmlspecialchars(\json_encode($response['args'], JSON_PRETTY_PRINT)) . '</pre>';
} else {
    echo '<p>Error: ' . $response['error']['message'] . '</p>';
}

echo '<hr><p><em>codesaur/http-client v2.1.0</em></p>';
