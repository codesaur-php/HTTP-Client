<?php

namespace codesaur\Http\Client\Example;

/* DEV: v1.2021.10.09
 * 
 * This is an example script!
 */

ini_set('display_errors', 'On');
error_reporting(\E_ALL & ~\E_STRICT & ~\E_NOTICE);

require_once '../vendor/autoload.php';

use codesaur\Http\Client\Client;

$client = new Client();
echo $client->request('www.google.com/');
