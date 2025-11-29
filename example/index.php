<?php

namespace codesaur\Http\Client\Example;

/**
 * JSONClient ашиглан POST хүсэлт илгээх жишээ.
 *
 * Энэ скрипт нь JSONClient класс ашиглан
 * https://httpbin.org/post руу JSON POST хүсэлт илгээж,
 * серверийн буцаасан хариуг шалгах зориулалттай.
 *
 * httpbin.org нь туршилтын зориулалттай, олон жил тогтвортой ажилласан
 * API тул хөгжүүлэлтийн үеийн жишээ болгон ашиглахад тохиромжтой.
 */

\ini_set('display_errors', 'On');
\error_reporting(E_ALL);

require_once '../vendor/autoload.php';

\header('Content-Type: application/json');

use codesaur\Http\Client\JSONClient;

// JSON клиент үүсгэнэ
$client = new JSONClient();

/**
 * POST хүсэлт илгээж туршиж үзэх.
 *
 * Илгээх payload:
 *   ['test' => 'codesaur']
 *
 * JSONClient нь payload-ийг автоматаар JSON болгон хувиргана.
 */
$response = $client->post(
    'https://httpbin.org/post',
    ['test' => 'codesaur']
);

// Серверийн JSON хариуг хэвлэнэ
echo \json_encode($response)
    ?: '{"error":"Can\'t encode response!"}';
