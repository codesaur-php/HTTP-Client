<?php

namespace codesaur\Http\Client\Example;

/* DEV: v1.2021.10.09
 * 
 * This is an example script!
 */

\ini_set('display_errors', 'On');
\error_reporting(\E_ALL);

require_once '../vendor/autoload.php';

\header('Content-Type: application/json');

use codesaur\Http\Client\JSONClient;

$client = new JSONClient();
$response = $client->get('http://echo.jsontest.com/it\'s/a/wonderful/life');
echo \json_encode($response) ?: '{"error":"Can\'t encode response!"}';
